<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Tag;
use App\Models\User;
use App\Models\ImportHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoodreadsImportService
{
    private User $user;
    private ImportHistory $importHistory;
    private string $importTag;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Parse CSV file and return preview data
     */
    public function parseCSV(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Unable to open file');
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception('Invalid CSV file - no headers found');
        }

        // Normalize headers to lowercase for case-insensitive matching
        $headers = array_map('strtolower', $headers);

        $rows = [];
        $lineNumber = 1;

        while (($row = fgetcsv($handle)) !== false && $lineNumber < 100) { // Preview first 100
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
            $lineNumber++;
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'preview' => array_slice($rows, 0, 10), // Show first 10 for preview
            'total_rows' => $lineNumber - 1,
        ];
    }

    /**
     * Import books from CSV file
     */
    public function import(string $filePath, string $filename): ImportHistory
    {
        // Create import tag
        $this->importTag = 'import' . now()->timestamp;

        // Create import history record
        $this->importHistory = ImportHistory::create([
            'user_id' => $this->user->id,
            'source' => 'goodreads',
            'filename' => $filename,
            'import_tag' => $this->importTag,
            'status' => 'pending',
        ]);

        try {
            $this->importHistory->markAsProcessing();

            // Parse CSV
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception('Unable to open file');
            }

            $headers = fgetcsv($handle);
            if (!$headers) {
                throw new \Exception('Invalid CSV file');
            }

            $headers = array_map('strtolower', $headers);
            $totalRows = 0;
            $errors = [];

            DB::beginTransaction();

            while (($row = fgetcsv($handle)) !== false) {
                $totalRows++;
                
                if (count($row) !== count($headers)) {
                    $errors[] = "Line $totalRows: Column count mismatch";
                    $this->importHistory->incrementFailed();
                    continue;
                }

                $data = array_combine($headers, $row);

                try {
                    $this->importBook($data, $totalRows);
                } catch (\Exception $e) {
                    $errors[] = "Line $totalRows: " . $e->getMessage();
                    $this->importHistory->incrementFailed();
                    Log::error("Import error on line $totalRows", [
                        'error' => $e->getMessage(),
                        'data' => $data,
                    ]);
                }
            }

            fclose($handle);

            // Update total rows
            $this->importHistory->update([
                'total_rows' => $totalRows,
                'errors' => array_slice($errors, 0, 100), // Keep first 100 errors
            ]);

            DB::commit();
            $this->importHistory->markAsCompleted();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->importHistory->markAsFailed([$e->getMessage()]);
            throw $e;
        }

        return $this->importHistory;
    }

    /**
     * Import a single book from CSV row
     */
    private function importBook(array $data, int $lineNumber): void
    {
        // Sanitize ISBN fields (remove Excel formula protection)
        $isbn = $this->sanitizeISBN($data['isbn'] ?? '');
        $isbn13 = $this->sanitizeISBN($data['isbn13'] ?? '');
        $title = trim($data['title'] ?? '');
        $author = trim($data['author'] ?? '');

        if (empty($title) || empty($author)) {
            throw new \Exception("Missing required fields (title or author)");
        }

        // Check if book already exists for this user
        $existingBook = $this->findExistingBook($isbn, $isbn13, $title, $author);

        if ($existingBook) {
            $this->importHistory->incrementSkipped();
            // Still add import tag to existing book
            $this->addImportTag($existingBook);
            return;
        }

        // Map Goodreads shelf to status
        $status = $this->mapShelfToStatus($data['exclusive shelf'] ?? 'to-read');

        // Parse dates
        $dateRead = $this->parseDate($data['date read'] ?? '');
        $dateAdded = $this->parseDate($data['date added'] ?? '');

        // Create book
        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => $title,
            'author' => $this->combineAuthors($data),
            'isbn' => $isbn ?: null,
            'isbn13' => $isbn13 ?: null,
            'publisher' => trim($data['publisher'] ?? '') ?: null,
            'published_date' => $this->parsePublishedYear($data),
            'page_count' => $this->parseInt($data['number of pages'] ?? ''),
            'rating' => $this->parseRating($data['my rating'] ?? ''),
            'review' => $this->combineReviewAndNotes($data),
            'status' => $status,
            'added_at' => $dateAdded ?: now(),
            'started_at' => $status === 'currently_reading' ? ($dateAdded ?: now()) : null,
            'finished_at' => $status === 'read' ? $dateRead : null,
            'api_source' => 'goodreads_import',
        ]);

        // Add import tag
        $this->addImportTag($book);

        $this->importHistory->incrementImported();
    }

    /**
     * Find existing book by ISBN or title+author
     */
    private function findExistingBook(?string $isbn, ?string $isbn13, string $title, string $author): ?Book
    {
        // Try ISBN13 first (most reliable)
        if ($isbn13) {
            $book = $this->user->books()->where('isbn13', $isbn13)->first();
            if ($book) return $book;
        }

        // Try ISBN
        if ($isbn) {
            $book = $this->user->books()->where('isbn', $isbn)->first();
            if ($book) return $book;
        }

        // Fallback: Match by title and author
        return $this->user->books()
            ->where('title', $title)
            ->where('author', 'LIKE', '%' . $author . '%')
            ->first();
    }

    /**
     * Sanitize ISBN (remove Excel formula protection)
     */
    private function sanitizeISBN(string $isbn): string
    {
        // Remove Excel formula format: ="0123456789"
        $isbn = preg_replace('/^="?(.*?)"?$/', '$1', $isbn);
        // Remove any remaining quotes and whitespace
        $isbn = trim($isbn, '"\'');
        return preg_replace('/[^0-9X]/', '', $isbn);
    }

    /**
     * Map Goodreads shelf to Leafmark status
     */
    private function mapShelfToStatus(string $shelf): string
    {
        return match (strtolower(trim($shelf))) {
            'read' => 'read',
            'currently-reading' => 'currently_reading',
            'to-read', 'want-to-read' => 'want_to_read',
            default => 'want_to_read',
        };
    }

    /**
     * Combine primary author and additional authors
     */
    private function combineAuthors(array $data): string
    {
        $author = trim($data['author'] ?? '');
        $additionalAuthors = trim($data['additional authors'] ?? '');

        if ($additionalAuthors) {
            return $author . ', ' . $additionalAuthors;
        }

        return $author;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate(string $date): ?Carbon
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse published year
     */
    private function parsePublishedYear(array $data): ?Carbon
    {
        $year = trim($data['year published'] ?? $data['original publication year'] ?? '');
        
        if (empty($year) || !is_numeric($year)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y', $year);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse integer field
     */
    private function parseInt(string $value): ?int
    {
        $value = preg_replace('/[^0-9]/', '', $value);
        return $value ? (int) $value : null;
    }

    /**
     * Parse rating (Goodreads uses 0-5 stars, Leafmark uses 0-5 as well)
     */
    private function parseRating(string $rating): ?float
    {
        $rating = trim($rating);
        if (empty($rating) || $rating === '0') {
            return null;
        }
        return min(5, max(0, (float) $rating));
    }

    /**
     * Combine review and private notes
     */
    private function combineReviewAndNotes(array $data): ?string
    {
        $review = trim($data['my review'] ?? '');
        $notes = trim($data['private notes'] ?? '');

        if ($review && $notes) {
            return $review . "\n\n--- Private Notes ---\n" . $notes;
        }

        return $review ?: $notes ?: null;
    }

    /**
     * Add import tag to book
     */
    private function addImportTag(Book $book): void
    {
        $tag = Tag::firstOrCreate(
            [
                'user_id' => $this->user->id,
                'name' => $this->importTag,
            ],
            [
                'description' => 'Imported from Goodreads on ' . now()->format('Y-m-d H:i'),
                'color' => '#6366f1', // Indigo
                'is_default' => false,
            ]
        );

        $tag->addBook($book);
    }
}
