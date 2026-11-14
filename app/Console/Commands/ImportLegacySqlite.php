<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Einmaliger Umzug der alten SQLite-Datenbank in die aktuelle Datenbank (Postgres).
 *
 * Kopiert alle Tabellen 1:1 inklusive IDs, damit Book-Route-Keys, Cover-Pfade und
 * Pivot-Tabellen gültig bleiben. Anleitung: deploy/MIGRATION.md
 */
class ImportLegacySqlite extends Command
{
    protected $signature = 'leafmark:import-sqlite
                            {path : Pfad zur alten database.sqlite}
                            {--force : Import even if the target already has users (their data is DELETED)}';

    protected $description = 'Copy all data from a legacy SQLite database into the current database';

    private const SOURCE = 'legacy_sqlite';

    /** Tabellen, die nicht kopiert werden (Schema kommt aus den Migrationen). */
    private const SKIP_TABLES = ['migrations', 'sqlite_sequence'];

    private const CHUNK = 500;

    /** Spalten, die erst nach allen Tabellen gesetzt werden (Fremdschlüssel-Zyklus). */
    private const DEFERRED_COLUMNS = ['users' => ['family_id']];

    /** @var array<string, array<string, array<int, mixed>>> */
    private array $deferred = [];

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_file($path) || ! is_readable($path)) {
            $this->error("SQLite file not found or not readable: {$path}");

            return self::FAILURE;
        }

        config(['database.connections.'.self::SOURCE => [
            'driver' => 'sqlite',
            'database' => $path,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);

        $source = DB::connection(self::SOURCE);
        $target = DB::connection();

        if (! Schema::hasTable('users')) {
            $this->error('Target database has no schema. Run "php artisan migrate" first.');

            return self::FAILURE;
        }

        if ($target->table('users')->exists() && ! $this->option('force')) {
            $this->error('Target database already contains users. Use --force to import anyway.');

            return self::FAILURE;
        }

        $tables = $this->orderedTables($source, $target);
        $this->info('Importing '.count($tables).' tables from '.$path.' into '.$target->getDriverName().' …');

        $counts = [];

        $target->transaction(function () use ($tables, $source, $target, &$counts) {
            // Ziel leeren: Migrationen legen Standardzeilen an (z. B. system_settings), die Quelle ist maßgeblich.
            foreach (array_reverse($tables) as $table) {
                $target->table($table)->delete();
            }

            foreach ($tables as $table) {
                $counts[$table] = $this->copyTable($source, $target, $table);
            }

            // Zirkuläre Beziehung users ↔ families: family_id erst setzen, wenn die Familien existieren.
            foreach ($this->deferred as $table => $columns) {
                foreach ($columns as $column => $values) {
                    foreach ($values as $id => $value) {
                        $target->table($table)->where('id', $id)->update([$column => $value]);
                    }
                }
            }
        });

        $this->resetSequences($target, $tables);

        $rows = [];
        $ok = true;
        foreach ($counts as $table => $copied) {
            $inTarget = $target->table($table)->count();
            $match = $inTarget === $copied['source'];
            $ok = $ok && $match;
            $rows[] = [$table, $copied['source'], $inTarget, $match ? 'OK' : 'MISMATCH'];
        }
        $this->table(['Table', 'SQLite', 'Target', 'Status'], $rows);

        if (! $ok) {
            $this->error('Row counts differ. Check the output above before going live.');

            return self::FAILURE;
        }

        $this->info('Import complete.');

        return self::SUCCESS;
    }

    /**
     * Tabellen, die in Quelle und Ziel existieren; Eltern-Tabellen zuerst.
     */
    private function orderedTables(Connection $source, Connection $target): array
    {
        $sourceTables = collect($source->select("SELECT name FROM sqlite_master WHERE type = 'table'"))
            ->pluck('name')
            ->reject(fn ($t) => in_array($t, self::SKIP_TABLES, true))
            ->filter(fn ($t) => Schema::connection($target->getName())->hasTable($t))
            ->values()
            ->all();

        $priority = ['users', 'families', 'system_settings', 'books', 'tags'];

        usort($sourceTables, function ($a, $b) use ($priority) {
            $pa = array_search($a, $priority, true);
            $pb = array_search($b, $priority, true);

            return ($pa === false ? 99 : $pa) <=> ($pb === false ? 99 : $pb) ?: strcmp($a, $b);
        });

        return $sourceTables;
    }

    /**
     * @return array{source: int}
     */
    private function copyTable(Connection $source, Connection $target, string $table): array
    {
        $schema = Schema::connection($target->getName());
        $sourceColumns = collect($source->select("PRAGMA table_info(\"{$table}\")"))->pluck('name')->all();
        $targetColumns = collect($schema->getColumns($table))->keyBy('name');

        $columns = array_values(array_filter($sourceColumns, fn ($c) => $targetColumns->has($c)));
        $total = $source->table($table)->count();

        $this->output->write(sprintf('  %-28s %6d rows … ', $table, $total));

        $query = $source->table($table)->select($columns);
        in_array('id', $columns, true) ? $query->orderBy('id') : $query->orderBy($columns[0]);

        $deferredColumns = self::DEFERRED_COLUMNS[$table] ?? [];

        $query->chunk(self::CHUNK, function ($rows) use ($target, $table, $targetColumns, $deferredColumns) {
            $payload = $rows->map(function ($row) use ($table, $targetColumns, $deferredColumns) {
                $converted = [];
                foreach ((array) $row as $column => $value) {
                    if (in_array($column, $deferredColumns, true) && $value !== null) {
                        $this->deferred[$table][$column][$row->id] = $value;
                        $converted[$column] = null;
                        continue;
                    }
                    $converted[$column] = $this->convertValue($value, $targetColumns[$column]['type_name'] ?? '');
                }

                return $converted;
            })->all();

            $target->table($table)->insert($payload);
        });

        $this->output->writeln('done');

        return ['source' => $total];
    }

    /**
     * SQLite ist typlos; Postgres nicht. Booleans kommen als 0/1, leere Strings in Datumsspalten.
     */
    private function convertValue(mixed $value, string $type): mixed
    {
        $type = strtolower($type);

        if ($value === null) {
            return null;
        }

        if (in_array($type, ['bool', 'boolean'], true)) {
            return (bool) $value;
        }

        $nullIfEmpty = ['date', 'timestamp', 'timestamptz', 'datetime', 'time',
            'int2', 'int4', 'int8', 'integer', 'bigint', 'smallint', 'numeric', 'decimal',
            'float4', 'float8', 'real', 'double', 'json', 'jsonb'];

        if ($value === '' && in_array($type, $nullIfEmpty, true)) {
            return null;
        }

        return $value;
    }

    private function resetSequences(Connection $target, array $tables): void
    {
        if ($target->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($tables as $table) {
            // Nur Auto-Increment-IDs haben eine Sequenz (sessions.id ist z. B. ein String)
            $id = collect(Schema::connection($target->getName())->getColumns($table))->firstWhere('name', 'id');
            if (! $id || ! ($id['auto_increment'] ?? false)) {
                continue;
            }

            $target->statement(
                "SELECT setval(pg_get_serial_sequence(?, 'id'), COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM \"{$table}\"",
                [$table]
            );
        }
    }
}
