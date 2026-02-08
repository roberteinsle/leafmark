<?php

namespace App\Services;

use App\Models\Book;
use App\Models\ImportHistory;
use App\Models\ReadingChallenge;
use App\Models\ReadingProgressHistory;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class LibraryImportService
{
    private User $user;
    private ImportHistory $importHistory;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function validateAndPreview(string $zipPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['valid' => false, 'errors' => [__('app.library_transfer.invalid_zip')]];
        }

        // Security: check all entries for path traversal
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (!$this->isPathSafe($entryName)) {
                $zip->close();
                return ['valid' => false, 'errors' => [__('app.library_transfer.path_traversal')]];
            }
        }

        // Check for library.json
        $jsonContent = $zip->getFromName('library.json');
        if ($jsonContent === false) {
            $zip->close();
            return ['valid' => false, 'errors' => [__('app.library_transfer.missing_metadata')]];
        }

        $data = json_decode($jsonContent, true);
        if (!$data || !is_array($data)) {
            $zip->close();
            return ['valid' => false, 'errors' => [__('app.library_transfer.corrupted_json')]];
        }

        // Validate schema version
        $schemaVersion = $data['schema_version'] ?? 0;
        if ($schemaVersion !== 1) {
            $zip->close();
            return ['valid' => false, 'errors' => [__('app.library_transfer.unsupported_version', ['version' => $schemaVersion])]];
        }

        $zip->close();

        $books = $data['books'] ?? [];
        $booksPreview = array_slice($books, 0, 10);

        return [
            'valid' => true,
            'errors' => [],
            'schema_version' => $schemaVersion,
            'exported_at' => $data['exported_at'] ?? null,
            'user_name' => $data['user']['name'] ?? null,
            'statistics' => $data['statistics'] ?? [
                'total_books' => count($books),
                'total_tags' => count($data['tags'] ?? []),
                'total_covers' => 0,
                'total_challenges' => count($data['reading_challenges'] ?? []),
            ],
            'books_preview' => array_map(fn($b) => [
                'title' => $b['title'] ?? 'N/A',
                'author' => $b['author'] ?? 'N/A',
                'status' => $b['status'] ?? 'want_to_read',
                'rating' => $b['rating'] ?? null,
            ], $booksPreview),
        ];
    }

    public function import(string $zipPath, string $duplicateStrategy = 'skip'): ImportHistory
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Cannot open ZIP file');
        }

        $jsonContent = $zip->getFromName('library.json');
        $data = json_decode($jsonContent, true);

        $this->importHistory = ImportHistory::create([
            'user_id' => $this->user->id,
            'source' => 'leafmark_zip',
            'filename' => 'leafmark_export.zip',
            'status' => 'pending',
            'total_rows' => count($data['books'] ?? []),
        ]);

        $storedFiles = [];

        try {
            $this->importHistory->markAsProcessing();

            DB::beginTransaction();

            // Import tags first - build ref_id -> Tag map
            $tagMap = $this->importTags($data['tags'] ?? []);

            // Import books
            $errors = [];
            foreach (($data['books'] ?? []) as $index => $bookData) {
                try {
                    $this->importBook($bookData, $zip, $tagMap, $duplicateStrategy, $index, $storedFiles);
                } catch (\Exception $e) {
                    $errors[] = "Book #{$index} ({$bookData['title']}): " . $e->getMessage();
                    $this->importHistory->incrementFailed();
                    Log::error("Library import error on book #{$index}", [
                        'error' => $e->getMessage(),
                        'book' => $bookData['title'] ?? 'unknown',
                    ]);
                }
            }

            // Import reading challenges
            $this->importChallenges($data['reading_challenges'] ?? []);

            if (count($errors) > 0) {
                $this->importHistory->update([
                    'errors' => array_slice($errors, 0, 100),
                ]);
            }

            DB::commit();
            $this->importHistory->markAsCompleted();

        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up any stored files on rollback
            foreach ($storedFiles as $path) {
                Storage::disk('public')->delete($path);
            }

            $this->importHistory->markAsFailed([$e->getMessage()]);
            $zip->close();
            throw $e;
        }

        $zip->close();

        return $this->importHistory;
    }

    private function importTags(array $tagsData): array
    {
        $tagMap = [];

        foreach ($tagsData as $tagData) {
            $refId = $tagData['ref_id'] ?? null;
            if (!$refId) continue;

            $existing = $this->user->tags()->where('name', $tagData['name'])->first();

            if ($existing) {
                $tagMap[$refId] = $existing;
            } else {
                $tag = Tag::create([
                    'user_id' => $this->user->id,
                    'name' => $tagData['name'],
                    'description' => $tagData['description'] ?? null,
                    'color' => $tagData['color'] ?? '#6366f1',
                    'is_default' => $tagData['is_default'] ?? false,
                    'sort_order' => $tagData['sort_order'] ?? 0,
                ]);
                $tagMap[$refId] = $tag;
            }
        }

        return $tagMap;
    }

    private function importBook(array $bookData, ZipArchive $zip, array $tagMap, string $duplicateStrategy, int $index, array &$storedFiles): void
    {
        $title = $bookData['title'] ?? '';
        if (empty($title)) {
            throw new \Exception('Missing title');
        }

        $existingBook = $this->findExistingBook($bookData);

        if ($existingBook) {
            if ($duplicateStrategy === 'skip') {
                $this->importHistory->incrementSkipped();
                return;
            }

            if ($duplicateStrategy === 'overwrite') {
                $this->overwriteBook($existingBook, $bookData, $zip, $tagMap, $storedFiles);
                $this->importHistory->incrementOverwritten();
                return;
            }

            // keep_both: fall through to create new
        }

        $bookFields = $this->mapBookFields($bookData);

        // For keep_both with duplicate, use unique added_at
        if ($existingBook && $duplicateStrategy === 'keep_both') {
            $bookFields['added_at'] = now()->addMilliseconds($index);
        }

        $book = $this->user->books()->create($bookFields);

        // Import covers
        $this->importCovers($book, $bookData['covers'] ?? [], $zip, $storedFiles);

        // Import progress history
        $this->importProgressHistory($book, $bookData['progress_history'] ?? []);

        // Attach tags
        $this->attachTags($book, $bookData['tag_refs'] ?? [], $tagMap);

        $this->importHistory->incrementImported();
    }

    private function overwriteBook(Book $book, array $bookData, ZipArchive $zip, array $tagMap, array &$storedFiles): void
    {
        // Update book fields
        $book->update($this->mapBookFields($bookData));

        // Replace covers
        foreach ($book->covers as $oldCover) {
            Storage::disk('public')->delete($oldCover->path);
            $oldCover->delete();
        }
        $this->importCovers($book, $bookData['covers'] ?? [], $zip, $storedFiles);

        // Replace progress history
        $book->progressHistory()->delete();
        $this->importProgressHistory($book, $bookData['progress_history'] ?? []);

        // Replace tags
        $book->tags()->detach();
        $this->attachTags($book, $bookData['tag_refs'] ?? [], $tagMap);
    }

    private function mapBookFields(array $bookData): array
    {
        $fields = [
            'title' => $bookData['title'],
            'author' => $bookData['author'] ?? null,
            'isbn' => $bookData['isbn'] ?? null,
            'isbn13' => $bookData['isbn13'] ?? null,
            'publisher' => $bookData['publisher'] ?? null,
            'published_date' => $this->parseDate($bookData['published_date'] ?? null),
            'description' => $bookData['description'] ?? null,
            'page_count' => $bookData['page_count'] ?? null,
            'language' => $bookData['language'] ?? null,
            'current_page' => $bookData['current_page'] ?? 0,
            'status' => $bookData['status'] ?? 'want_to_read',
            'format' => $bookData['format'] ?? null,
            'series' => $bookData['series'] ?? null,
            'series_position' => $bookData['series_position'] ?? null,
            'rating' => $bookData['rating'] ?? null,
            'review' => $bookData['review'] ?? null,
            'purchase_date' => $this->parseDate($bookData['purchase_date'] ?? null),
            'purchase_price' => $bookData['purchase_price'] ?? null,
            'added_at' => $this->parseDateTime($bookData['added_at'] ?? null) ?? now(),
            'started_at' => $this->parseDateTime($bookData['started_at'] ?? null),
            'finished_at' => $this->parseDateTime($bookData['finished_at'] ?? null),
            'cover_url' => $bookData['cover_url'] ?? null,
            'thumbnail' => $bookData['thumbnail'] ?? null,
            'api_source' => $bookData['api_source'] ?? null,
            'external_id' => $bookData['external_id'] ?? null,
            'openlibrary_edition_id' => $bookData['openlibrary_edition_id'] ?? null,
            'goodreads_id' => $bookData['goodreads_id'] ?? null,
            'librarything_id' => $bookData['librarything_id'] ?? null,
            'openlibrary_url' => $bookData['openlibrary_url'] ?? null,
        ];

        // Only include purchase_currency if present (column has a NOT NULL default)
        if (isset($bookData['purchase_currency'])) {
            $fields['purchase_currency'] = $bookData['purchase_currency'];
        }

        return $fields;
    }

    private function importCovers(Book $book, array $coverEntries, ZipArchive $zip, array &$storedFiles): void
    {
        foreach ($coverEntries as $coverEntry) {
            $imageFile = $coverEntry['image_file'] ?? null;
            if (!$imageFile) continue;

            $content = $zip->getFromName($imageFile);
            if ($content === false) {
                Log::warning('Cover image not found in ZIP', ['image_file' => $imageFile]);
                continue;
            }

            // Validate MIME type
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($content);
            if (!str_starts_with($mimeType, 'image/')) {
                Log::warning('ZIP entry is not an image', ['image_file' => $imageFile, 'mime_type' => $mimeType]);
                continue;
            }

            $ext = pathinfo($imageFile, PATHINFO_EXTENSION) ?: 'jpg';
            $filename = 'covers/import_' . $book->id . '_' . uniqid() . '.' . $ext;

            Storage::disk('public')->makeDirectory('covers');
            Storage::disk('public')->put($filename, $content);
            $storedFiles[] = $filename;

            $book->covers()->create([
                'path' => $filename,
                'is_primary' => $coverEntry['is_primary'] ?? false,
                'sort_order' => $coverEntry['sort_order'] ?? 0,
            ]);

            // Also set local_cover_path for the first/primary cover
            if ($coverEntry['is_primary'] ?? false) {
                $book->update(['local_cover_path' => $filename]);
            }
        }
    }

    private function importProgressHistory(Book $book, array $entries): void
    {
        foreach ($entries as $entry) {
            ReadingProgressHistory::create([
                'book_id' => $book->id,
                'page_number' => $entry['page_number'] ?? 0,
                'recorded_at' => $this->parseDateTime($entry['recorded_at'] ?? null) ?? now(),
            ]);
        }
    }

    private function importChallenges(array $challenges): void
    {
        foreach ($challenges as $challenge) {
            $year = $challenge['year'] ?? null;
            $goal = $challenge['goal'] ?? null;
            if (!$year || !$goal) continue;

            // Skip if challenge for this year already exists
            $existing = $this->user->readingChallenges()->where('year', $year)->first();
            if ($existing) continue;

            ReadingChallenge::create([
                'user_id' => $this->user->id,
                'year' => $year,
                'goal' => $goal,
            ]);
        }
    }

    private function attachTags(Book $book, array $tagRefs, array $tagMap): void
    {
        foreach ($tagRefs as $refId) {
            if (isset($tagMap[$refId])) {
                $tagMap[$refId]->addBook($book);
            }
        }
    }

    private function findExistingBook(array $bookData): ?Book
    {
        $isbn13 = $bookData['isbn13'] ?? null;
        $isbn = $bookData['isbn'] ?? null;
        $title = $bookData['title'] ?? '';
        $author = $bookData['author'] ?? '';

        if ($isbn13) {
            $book = $this->user->books()->where('isbn13', $isbn13)->first();
            if ($book) return $book;
        }

        if ($isbn) {
            $book = $this->user->books()->where('isbn', $isbn)->first();
            if ($book) return $book;
        }

        if ($title && $author) {
            return $this->user->books()
                ->where('title', $title)
                ->where('author', 'LIKE', '%' . $author . '%')
                ->first();
        }

        return null;
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDateTime(?string $value): ?Carbon
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function isPathSafe(string $path): bool
    {
        if (str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            return false;
        }
        if (str_contains($path, '..')) {
            return false;
        }
        if ($path === 'library.json') {
            return true;
        }
        if (str_starts_with($path, 'images/')) {
            return true;
        }
        return false;
    }
}
