<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCover;
use App\Models\ReadingChallenge;
use App\Models\ReadingProgressHistory;
use App\Models\Tag;
use App\Services\LibraryExportService;
use App\Services\LibraryImportService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class LibraryTransferTest extends TestCase
{
    // ─── Export Tests ───

    public function test_guest_cannot_export(): void
    {
        $this->get('/library/export')->assertRedirect('/login');
    }

    public function test_user_can_download_export_zip(): void
    {
        $user = $this->actingAsUser();
        Book::factory()->create(['user_id' => $user->id]);

        $response = $this->get('/library/export');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
    }

    public function test_export_contains_valid_library_json(): void
    {
        $user = $this->actingAsUser();
        Book::factory()->count(3)->create(['user_id' => $user->id]);
        Tag::factory()->create(['user_id' => $user->id, 'name' => 'Fiction']);

        $service = new LibraryExportService($user);
        $data = $service->buildExportData();

        $this->assertEquals(1, $data['schema_version']);
        $this->assertCount(3, $data['books']);
        $this->assertCount(1, $data['tags']);
        $this->assertEquals('Fiction', $data['tags'][0]['name']);
        $this->assertEquals(3, $data['statistics']['total_books']);
    }

    public function test_export_empty_library_produces_valid_data(): void
    {
        $user = $this->actingAsUser();

        $service = new LibraryExportService($user);
        $data = $service->buildExportData();

        $this->assertEquals(1, $data['schema_version']);
        $this->assertCount(0, $data['books']);
        $this->assertCount(0, $data['tags']);
        $this->assertEquals(0, $data['statistics']['total_books']);
    }

    public function test_export_includes_progress_history(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->currentlyReading()->create([
            'user_id' => $user->id,
        ]);

        ReadingProgressHistory::create([
            'book_id' => $book->id,
            'page_number' => 50,
            'recorded_at' => now(),
        ]);

        $service = new LibraryExportService($user);
        $data = $service->buildExportData();

        $this->assertCount(1, $data['books'][0]['progress_history']);
        $this->assertEquals(50, $data['books'][0]['progress_history'][0]['page_number']);
    }

    public function test_export_includes_tag_refs(): void
    {
        $user = $this->actingAsUser();
        $tag = Tag::factory()->create(['user_id' => $user->id, 'name' => 'Sci-Fi']);
        $book = Book::factory()->create(['user_id' => $user->id]);
        $tag->addBook($book);

        $service = new LibraryExportService($user);
        $data = $service->buildExportData();

        $this->assertCount(1, $data['books'][0]['tag_refs']);
        $this->assertEquals($data['tags'][0]['ref_id'], $data['books'][0]['tag_refs'][0]);
    }

    public function test_export_includes_challenges(): void
    {
        $user = $this->actingAsUser();
        ReadingChallenge::create([
            'user_id' => $user->id,
            'year' => 2026,
            'goal' => 24,
        ]);

        $service = new LibraryExportService($user);
        $data = $service->buildExportData();

        $this->assertCount(1, $data['reading_challenges']);
        $this->assertEquals(2026, $data['reading_challenges'][0]['year']);
        $this->assertEquals(24, $data['reading_challenges'][0]['goal']);
    }

    // ─── Import Tests ───

    public function test_guest_cannot_access_import(): void
    {
        $this->get('/library/import')->assertRedirect('/login');
    }

    public function test_user_can_view_import_page(): void
    {
        $this->actingAsUser();
        $this->get('/library/import')->assertStatus(200);
    }

    public function test_invalid_zip_rejected(): void
    {
        $user = $this->actingAsUser();

        // Create a non-ZIP file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'not a zip file');

        $response = $this->post('/library/import/upload', [
            'zip_file' => new \Illuminate\Http\UploadedFile($tempFile, 'fake.zip', 'application/zip', null, true),
        ]);

        $response->assertSessionHasErrors('zip_file');
        @unlink($tempFile);
    }

    public function test_zip_missing_library_json_rejected(): void
    {
        $user = $this->actingAsUser();
        $zipPath = $this->createTestZip([]);

        $service = new LibraryImportService($user);
        $result = $service->validateAndPreview($zipPath);

        $this->assertFalse($result['valid']);
        @unlink($zipPath);
    }

    public function test_path_traversal_in_zip_rejected(): void
    {
        $user = $this->actingAsUser();

        $zipPath = tempnam(sys_get_temp_dir(), 'test_');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('library.json', json_encode(['schema_version' => 1, 'books' => []]));
        $zip->addFromString('../../../etc/passwd', 'malicious');
        $zip->close();

        $service = new LibraryImportService($user);
        $result = $service->validateAndPreview($zipPath);

        $this->assertFalse($result['valid']);
        @unlink($zipPath);
    }

    public function test_round_trip_export_import(): void
    {
        Storage::fake('public');

        $user = $this->actingAsUser();

        // Create test data
        $tag = Tag::factory()->create(['user_id' => $user->id, 'name' => 'TestTag']);
        $book = Book::factory()->read()->create([
            'user_id' => $user->id,
            'title' => 'Export Test Book',
            'author' => 'Test Author',
            'rating' => 4.5,
        ]);
        $tag->addBook($book);

        ReadingProgressHistory::create([
            'book_id' => $book->id,
            'page_number' => 100,
            'recorded_at' => now()->subDays(5),
        ]);

        ReadingChallenge::create([
            'user_id' => $user->id,
            'year' => 2026,
            'goal' => 12,
        ]);

        // Export
        $exportService = new LibraryExportService($user);
        $zipPath = $exportService->createZipArchive();

        // Delete all user data
        $user->books()->delete();
        $user->tags()->delete();
        $user->readingChallenges()->delete();

        $this->assertEquals(0, $user->books()->count());

        // Import
        $importService = new LibraryImportService($user);
        $importHistory = $importService->import($zipPath, 'skip');

        // Verify
        $this->assertEquals('completed', $importHistory->status);
        $this->assertEquals(1, $importHistory->imported_count);
        $this->assertEquals(1, $user->books()->count());
        $this->assertEquals(1, $user->tags()->count());

        $importedBook = $user->books()->first();
        $this->assertEquals('Export Test Book', $importedBook->title);
        $this->assertEquals('Test Author', $importedBook->author);
        $this->assertEquals(4.5, (float) $importedBook->rating);

        $importedTag = $user->tags()->first();
        $this->assertEquals('TestTag', $importedTag->name);

        // Check tag assignment
        $this->assertTrue($importedBook->tags->contains('name', 'TestTag'));

        // Check progress history
        $this->assertEquals(1, $importedBook->progressHistory()->count());

        // Check challenge
        $this->assertEquals(1, $user->readingChallenges()->count());
        $challenge = $user->readingChallenges()->first();
        $this->assertEquals(2026, $challenge->year);
        $this->assertEquals(12, $challenge->goal);

        @unlink($zipPath);
    }

    public function test_import_skip_strategy_works(): void
    {
        Storage::fake('public');

        $user = $this->actingAsUser();

        // Create existing book
        $existingBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Existing Book',
            'author' => 'Author',
            'isbn13' => '9781234567890',
            'rating' => 3.0,
        ]);

        // Create ZIP with same book
        $zipPath = $this->createValidTestZip([
            'books' => [[
                'title' => 'Existing Book',
                'author' => 'Author',
                'isbn13' => '9781234567890',
                'rating' => 5.0,
                'status' => 'read',
                'covers' => [],
                'progress_history' => [],
                'tag_refs' => [],
            ]],
        ]);

        $importService = new LibraryImportService($user);
        $importHistory = $importService->import($zipPath, 'skip');

        $this->assertEquals(1, $importHistory->skipped_count);
        $this->assertEquals(0, $importHistory->imported_count);

        // Original rating should be unchanged
        $existingBook->refresh();
        $this->assertEquals(3.0, (float) $existingBook->rating);

        @unlink($zipPath);
    }

    public function test_import_overwrite_strategy_works(): void
    {
        Storage::fake('public');

        $user = $this->actingAsUser();

        // Create existing book
        $existingBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Overwrite Me',
            'author' => 'Author',
            'isbn13' => '9781234567891',
            'rating' => 2.0,
        ]);

        // Create ZIP with updated book
        $zipPath = $this->createValidTestZip([
            'books' => [[
                'title' => 'Overwrite Me',
                'author' => 'Author',
                'isbn13' => '9781234567891',
                'rating' => 5.0,
                'status' => 'read',
                'covers' => [],
                'progress_history' => [],
                'tag_refs' => [],
            ]],
        ]);

        $importService = new LibraryImportService($user);
        $importHistory = $importService->import($zipPath, 'overwrite');
        $importHistory->refresh();

        $this->assertEquals(1, $importHistory->overwritten_count);

        $existingBook->refresh();
        $this->assertEquals(5.0, (float) $existingBook->rating);

        @unlink($zipPath);
    }

    public function test_import_keep_both_strategy_works(): void
    {
        Storage::fake('public');

        $user = $this->actingAsUser();

        // Create existing book
        Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Keep Both',
            'author' => 'Author',
            'isbn13' => '9781234567892',
        ]);

        // Create ZIP with same book
        $zipPath = $this->createValidTestZip([
            'books' => [[
                'title' => 'Keep Both',
                'author' => 'Author',
                'isbn13' => '9781234567892',
                'status' => 'want_to_read',
                'covers' => [],
                'progress_history' => [],
                'tag_refs' => [],
            ]],
        ]);

        $importService = new LibraryImportService($user);
        $importHistory = $importService->import($zipPath, 'keep_both');
        $importHistory->refresh();

        $this->assertEquals(1, $importHistory->imported_count);
        $this->assertEquals(2, $user->books()->count());

        @unlink($zipPath);
    }

    public function test_import_deduplicates_tags_by_name(): void
    {
        Storage::fake('public');

        $user = $this->actingAsUser();

        // Create existing tag
        Tag::factory()->create(['user_id' => $user->id, 'name' => 'Fiction']);

        // Create ZIP with book referencing same tag name
        $zipPath = $this->createValidTestZip([
            'tags' => [
                ['ref_id' => 'tag_0', 'name' => 'Fiction', 'color' => '#ff0000', 'is_default' => false, 'sort_order' => 0],
            ],
            'books' => [[
                'title' => 'Tag Test Book',
                'author' => 'Author',
                'status' => 'want_to_read',
                'covers' => [],
                'progress_history' => [],
                'tag_refs' => ['tag_0'],
            ]],
        ]);

        $importService = new LibraryImportService($user);
        $importHistory = $importService->import($zipPath, 'skip');
        $importHistory->refresh();

        // Debug: check for errors
        $this->assertEmpty($importHistory->errors ?? [], 'Import had errors: ' . json_encode($importHistory->errors));
        $this->assertEquals(1, $importHistory->imported_count, 'Expected 1 imported book');

        // Should still only have 1 tag (reused existing)
        $this->assertEquals(1, $user->tags()->count());

        // The imported book should have the tag
        $importedBook = $user->books()->where('title', 'Tag Test Book')->first();
        $this->assertNotNull($importedBook);
        $this->assertTrue($importedBook->tags->contains('name', 'Fiction'));

        @unlink($zipPath);
    }

    public function test_import_cancelled(): void
    {
        $this->actingAsUser();

        // Store a fake temp file in default storage
        $tempPath = 'imports/temp/test_cancel.zip';
        Storage::put($tempPath, 'test');
        $this->assertTrue(Storage::exists($tempPath));

        $this->withSession(['library_import_path' => $tempPath]);

        $response = $this->post('/library/import/cancel');
        $response->assertRedirect(route('settings.edit', ['tab' => 'data']));

        $this->assertFalse(Storage::exists($tempPath));
    }

    // ─── Helpers ───

    private function createTestZip(array $entries): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'test_');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();
        return $zipPath;
    }

    private function createValidTestZip(array $overrides = []): string
    {
        $data = array_merge([
            'schema_version' => 1,
            'exported_at' => now()->toIso8601String(),
            'app_version' => '1.0.0',
            'user' => ['name' => 'Test User'],
            'statistics' => ['total_books' => 0, 'total_tags' => 0, 'total_covers' => 0, 'total_challenges' => 0],
            'tags' => [],
            'books' => [],
            'reading_challenges' => [],
        ], $overrides);

        // Update statistics from data
        $data['statistics']['total_books'] = count($data['books']);
        $data['statistics']['total_tags'] = count($data['tags']);

        $zipPath = tempnam(sys_get_temp_dir(), 'test_');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('library.json', json_encode($data));
        $zip->close();

        return $zipPath;
    }
}
