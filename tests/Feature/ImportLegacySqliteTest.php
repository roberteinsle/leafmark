<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCover;
use App\Models\Family;
use App\Models\SystemSetting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ImportLegacySqliteTest extends TestCase
{
    private string $legacyPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->legacyPath = tempnam(sys_get_temp_dir(), 'legacy').'.sqlite';
        touch($this->legacyPath);

        config(['database.connections.legacy_build' => [
            'driver' => 'sqlite',
            'database' => $this->legacyPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);

        Artisan::call('migrate', ['--database' => 'legacy_build', '--force' => true]);
        $this->seedLegacyDatabase();
    }

    protected function tearDown(): void
    {
        DB::purge('legacy_build');
        DB::purge('legacy_sqlite');
        @unlink($this->legacyPath);

        parent::tearDown();
    }

    private function seedLegacyDatabase(): void
    {
        $db = DB::connection('legacy_build');
        $now = now()->toDateTimeString();

        $db->table('users')->insert([
            ['id' => 7, 'name' => 'Owner', 'email' => 'owner@example.com', 'password' => 'x', 'is_admin' => 1, 'family_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'name' => 'Member', 'email' => 'member@example.com', 'password' => 'x', 'is_admin' => 0, 'family_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $db->table('families')->insert([
            'id' => 3, 'name' => 'Family', 'join_code' => 'ABCDEFGH', 'owner_id' => 7, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $db->table('users')->whereIn('id', [7, 9])->update(['family_id' => 3]);

        $db->table('books')->insert([
            'id' => 42, 'user_id' => 7, 'title' => 'Legacy Book', 'author' => 'Someone', 'status' => 'read',
            'added_at' => '2025-03-01 10:00:00', 'finished_at' => '2025-04-01 10:00:00', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $db->table('tags')->insert([
            'id' => 5, 'user_id' => 7, 'name' => 'Favourites', 'is_default' => 1, 'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $db->table('book_tag')->insert(['book_id' => 42, 'tag_id' => 5, 'created_at' => $now, 'updated_at' => $now]);
        $db->table('book_covers')->insert([
            'id' => 11, 'book_id' => 42, 'path' => 'covers/7/42/a.jpg', 'is_primary' => 1, 'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $db->table('system_settings')->updateOrInsert(['key' => 'registration_mode'], ['value' => 'code']);
    }

    public function test_copies_all_rows_with_ids_and_relations(): void
    {
        $this->artisan('leafmark:import-sqlite', ['path' => $this->legacyPath])
            ->assertSuccessful();

        $owner = User::find(7);
        $this->assertTrue($owner->is_admin);
        $this->assertFalse(User::find(9)->is_admin);
        $this->assertSame(3, $owner->family_id);
        $this->assertSame(7, Family::find(3)->owner_id);

        $book = Book::find(42);
        $this->assertSame('Legacy Book', $book->title);
        $this->assertSame(7, $book->user_id);
        $this->assertSame(['Favourites'], $book->tags->pluck('name')->all());
        $this->assertTrue(BookCover::find(11)->is_primary);
        $this->assertTrue(Tag::find(5)->is_default);

        // Quelle ist maßgeblich, auch für Einstellungen aus den Migrationen
        $this->assertSame('code', SystemSetting::get('registration_mode'));
    }

    public function test_new_rows_get_ids_after_imported_ones(): void
    {
        $this->artisan('leafmark:import-sqlite', ['path' => $this->legacyPath])->assertSuccessful();

        $user = User::factory()->create();

        $this->assertGreaterThan(9, $user->id);
    }

    public function test_refuses_when_target_already_has_users(): void
    {
        User::factory()->create();

        $this->artisan('leafmark:import-sqlite', ['path' => $this->legacyPath])
            ->assertFailed();

        $this->assertNull(Book::find(42));
    }

    public function test_fails_for_missing_file(): void
    {
        $this->artisan('leafmark:import-sqlite', ['path' => '/nope/missing.sqlite'])
            ->assertFailed();
    }
}
