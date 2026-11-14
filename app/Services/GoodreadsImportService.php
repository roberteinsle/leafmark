<?php

namespace App\Services;

use App\Models\Book;
use App\Models\ImportHistory;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Import aus dem Goodreads-CSV-Export ("My Books → Import and export → Export Library").
 *
 * - Exklusives Regal → Lesestatus; alle anderen Regale → Tags
 * - Autor = Spalte "Author" (in "Additional Authors" stehen auch Übersetzer und Sprecher)
 * - "Titel (Reihe, #3)" → Titel, Reihe, Band
 * - Bindung → Format, Erscheinungsjahr → JJJJ-01-01 (Fallback: ursprüngliches Jahr)
 * - Jede Zeile in einem eigenen Savepoint: Auf Postgres bricht ein SQL-Fehler sonst die ganze Transaktion ab
 */
class GoodreadsImportService
{
    /** Goodreads-Regale, die den Lesestatus bestimmen und nicht zu Tags werden */
    private const STATUS_SHELVES = ['read', 'currently-reading', 'to-read', 'want-to-read'];

    private const SHELF_TAG_COLOR = '#6b7280';
    private const IMPORT_TAG_COLOR = '#6366f1';

    /** Goodreads-Bindung (klein geschrieben) → books.format */
    private const FORMATS = [
        'kindle edition' => 'digital', 'ebook' => 'digital', 'e-book' => 'digital', 'nook' => 'digital', 'kobo' => 'digital',
        'hardcover' => 'hardcover', 'gebunden' => 'hardcover', 'gebundene ausgabe' => 'hardcover', 'library binding' => 'hardcover',
        'paperback' => 'paperback', 'taschenbuch' => 'paperback', 'broschiert' => 'paperback', 'mass market paperback' => 'paperback',
        'perfect paperback' => 'paperback', 'trade paperback' => 'paperback', 'kartoniert' => 'paperback',
        'audible audio' => 'audiobook', 'audiobook' => 'audiobook', 'audio cd' => 'audiobook', 'mp3 cd' => 'audiobook', 'hörbuch' => 'audiobook',
        'spiral-bound' => 'spiral_bound', 'leather bound' => 'leather_bound',
    ];

    private User $user;
    private ImportHistory $importHistory;
    private ?string $importTag = null;

    /** @var array<string, Tag> */
    private array $tagCache = [];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Vorschau: zählt alle Zeilen, zeigt die ersten 10.
     */
    public function parseCSV(string $filePath): array
    {
        [$handle, $headers] = $this->open($filePath);

        $preview = [];
        $total = 0;
        while (($row = $this->readRow($handle)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }
            $total++;
            if (count($preview) < 10 && count($row) === count($headers)) {
                $preview[] = array_combine($headers, $row);
            }
        }
        fclose($handle);

        return [
            'headers' => $headers,
            'preview' => $preview,
            'total_rows' => $total,
        ];
    }

    public function import(string $filePath, string $filename, bool $createImportTag = false): ImportHistory
    {
        $this->importTag = $createImportTag ? 'import'.now()->timestamp : null;

        $this->importHistory = ImportHistory::create([
            'user_id' => $this->user->id,
            'source' => 'goodreads',
            'filename' => $filename,
            'import_tag' => $this->importTag,
            'status' => 'pending',
        ]);

        try {
            $this->importHistory->markAsProcessing();
            [$handle, $headers] = $this->open($filePath);

            $line = 1; // Kopfzeile
            $totalRows = 0;
            $errors = [];

            DB::beginTransaction();

            while (($row = $this->readRow($handle)) !== false) {
                $line++;
                if ($this->isEmptyRow($row)) {
                    continue;
                }
                $totalRows++;

                if (count($row) !== count($headers)) {
                    $errors[] = "Line {$line}: Column count mismatch";
                    $this->importHistory->incrementFailed();
                    continue;
                }

                $data = array_combine($headers, $row);

                try {
                    // Savepoint pro Zeile: ein Fehler verwirft nur diese Zeile
                    DB::transaction(fn () => $this->importBook($data));
                } catch (\Throwable $e) {
                    $errors[] = "Line {$line}: ".$e->getMessage();
                    $this->importHistory->incrementFailed();
                    Log::warning("Goodreads import error on line {$line}", ['error' => $e->getMessage()]);
                }
            }

            fclose($handle);

            $this->importHistory->update([
                'total_rows' => $totalRows,
                'errors' => array_slice($errors, 0, 100),
            ]);

            DB::commit();
            $this->importHistory->markAsCompleted();
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->importHistory->markAsFailed([$e->getMessage()]);
            throw $e;
        }

        return $this->importHistory;
    }

    private function importBook(array $data): void
    {
        $isbn = $this->sanitizeISBN($data['isbn'] ?? '');
        $isbn13 = $this->sanitizeISBN($data['isbn13'] ?? '');
        $goodreadsId = $this->clean($data['book id'] ?? '') ?: null;
        [$title, $series, $seriesPosition] = $this->splitSeries($this->clean($data['title'] ?? ''));
        $author = $this->clean($data['author'] ?? '');

        if ($title === '' || $author === '') {
            throw new \RuntimeException('Missing required fields (title or author)');
        }

        $shelves = $this->shelves($data['bookshelves'] ?? '');

        $existingBook = $this->findExistingBook($goodreadsId, $isbn, $isbn13, $title, $author);
        if ($existingBook) {
            $this->importHistory->incrementSkipped();
            $this->attachTags($existingBook, $shelves);

            return;
        }

        $status = $this->mapShelfToStatus($data['exclusive shelf'] ?? 'to-read');
        $dateRead = $this->parseDate($data['date read'] ?? '');
        $dateAdded = $this->parseDate($data['date added'] ?? '');

        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => $title,
            'series' => $series,
            'series_position' => $seriesPosition,
            'author' => $author,
            'isbn' => $isbn ?: null,
            'isbn13' => $isbn13 ?: null,
            'publisher' => $this->clean($data['publisher'] ?? '') ?: null,
            'published_date' => $this->parsePublishedYear($data),
            'page_count' => $this->parseInt($data['number of pages'] ?? ''),
            'format' => self::FORMATS[mb_strtolower($this->clean($data['binding'] ?? ''))] ?? null,
            'rating' => $this->parseRating($data['my rating'] ?? ''),
            'review' => $this->combineReviewAndNotes($data),
            'status' => $status,
            'added_at' => $dateAdded ?: now(),
            'started_at' => $status === 'currently_reading' ? ($dateAdded ?: now()) : null,
            'finished_at' => $status === 'read' ? $dateRead : null,
            'api_source' => 'goodreads_import',
            'goodreads_id' => $goodreadsId,
        ]);

        $this->attachTags($book, $shelves);
        $this->importHistory->incrementImported();
    }

    /**
     * Dubletten: Goodreads-ID, ISBN-13, ISBN-10, sonst Titel + Autor.
     */
    private function findExistingBook(?string $goodreadsId, ?string $isbn, ?string $isbn13, string $title, string $author): ?Book
    {
        foreach (['goodreads_id' => $goodreadsId, 'isbn13' => $isbn13, 'isbn' => $isbn] as $column => $value) {
            if ($value) {
                $book = $this->user->books()->where($column, $value)->first();
                if ($book) {
                    return $book;
                }
            }
        }

        return $this->user->books()
            ->whereLike('title', $title)
            ->whereLike('author', '%'.$author.'%')
            ->first();
    }

    /**
     * "Harry Potter und der Feuerkelch (Harry Potter, #4)" → [Titel, "Harry Potter", 4]
     * "Der Herr der Ringe (The Lord of the Rings, #1-3)" → [Titel, Reihe, null] (Sammelband)
     */
    private function splitSeries(string $title): array
    {
        if (preg_match('/^(.+?)\s*\(([^()#]+?),?\s*#(\d+)(?:\.\d+)?(-\d+)?\)$/u', $title, $m)) {
            return [trim($m[1]), trim($m[2]), isset($m[4]) && $m[4] !== '' ? null : (int) $m[3]];
        }

        return [$title, null, null];
    }

    /** "to-read, dbaz100, dbaz10" → ["dbaz100", "dbaz10"] (ohne Status-Regale) */
    private function shelves(string $value): array
    {
        $names = array_map(fn ($s) => $this->clean($s), explode(',', $value));

        return array_values(array_unique(array_filter(
            $names,
            fn ($s) => $s !== '' && ! in_array(mb_strtolower($s), self::STATUS_SHELVES, true)
        )));
    }

    private function attachTags(Book $book, array $shelves): void
    {
        foreach ($shelves as $shelf) {
            $this->tag($shelf, self::SHELF_TAG_COLOR, null)->addBook($book);
        }

        if ($this->importTag) {
            $this->tag($this->importTag, self::IMPORT_TAG_COLOR, 'Imported from Goodreads on '.now()->format('Y-m-d H:i'))->addBook($book);
        }
    }

    private function tag(string $name, string $color, ?string $description): Tag
    {
        return $this->tagCache[$name] ??= Tag::firstOrCreate(
            ['user_id' => $this->user->id, 'name' => mb_substr($name, 0, 255)],
            ['description' => $description, 'color' => $color, 'is_default' => false]
        );
    }

    /**
     * Unsichtbare Formatzeichen (z. B. U+200E vor "22 Bahnen", BOM) entfernen,
     * Mehrfach-Leerzeichen ("Dan    Brown") zusammenfassen, Unicode auf NFC normalisieren.
     */
    private function clean(string $value): string
    {
        $value = preg_replace('/\p{Cf}+/u', '', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        if (class_exists(\Normalizer::class)) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_C) ?: $value;
        }

        return trim($value);
    }

    /** Goodreads schützt ISBNs als Excel-Formel: ="9783596164530" */
    private function sanitizeISBN(string $isbn): string
    {
        $isbn = preg_replace('/^="?(.*?)"?$/', '$1', $isbn);

        return preg_replace('/[^0-9X]/', '', strtoupper(trim($isbn, '"\'')));
    }

    private function mapShelfToStatus(string $shelf): string
    {
        return match (mb_strtolower(trim($shelf))) {
            'read' => 'read',
            'currently-reading' => 'currently_reading',
            default => 'want_to_read',
        };
    }

    private function parseDate(string $date): ?Carbon
    {
        $date = trim($date);
        if ($date === '') {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * "Year Published" (diese Ausgabe), sonst "Original Publication Year". Nur plausible
     * Jahreszahlen; Tippfehler wie "20015" fallen auf das ursprüngliche Jahr zurück.
     */
    private function parsePublishedYear(array $data): ?string
    {
        foreach (['year published', 'original publication year'] as $column) {
            $year = trim($data[$column] ?? '');
            if (preg_match('/^\d{4}$/', $year) && (int) $year >= 1000 && (int) $year <= (int) date('Y') + 1) {
                return $year.'-01-01';
            }
        }

        return null;
    }

    private function parseInt(string $value): ?int
    {
        $value = preg_replace('/[^0-9]/', '', $value);

        return $value !== '' && (int) $value > 0 ? (int) $value : null;
    }

    private function parseRating(string $rating): ?float
    {
        $value = (float) trim($rating);

        return $value > 0 ? min(5, $value) : null;
    }

    private function combineReviewAndNotes(array $data): ?string
    {
        $review = trim($data['my review'] ?? '');
        $notes = trim($data['private notes'] ?? '');

        if ($review && $notes) {
            return $review."\n\n--- Private Notes ---\n".$notes;
        }

        return $review ?: ($notes ?: null);
    }

    /**
     * @return array{0: resource, 1: array<int, string>}
     */
    private function open(string $filePath): array
    {
        $handle = @fopen($filePath, 'r');
        if (! $handle) {
            throw new \RuntimeException('Unable to open file');
        }

        $headers = $this->readRow($handle);
        if (! $headers) {
            fclose($handle);
            throw new \RuntimeException('Invalid CSV file - no headers found');
        }

        // BOM entfernen, Spaltennamen ohne Groß-/Kleinschreibung vergleichen
        $headers = array_map(fn ($h) => mb_strtolower($this->clean((string) $h)), $headers);

        return [$handle, $headers];
    }

    /** RFC-4180-CSV: Anführungszeichen werden verdoppelt, kein Backslash-Escape */
    private function readRow($handle): array|false
    {
        return fgetcsv($handle, null, ',', '"', '');
    }

    private function isEmptyRow(array $row): bool
    {
        return count($row) === 1 && trim((string) $row[0]) === '';
    }
}
