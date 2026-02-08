<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class LibraryExportService
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function createZipArchive(): string
    {
        $exportData = $this->buildExportData();
        $coverFiles = $this->collectCoverFiles($exportData);

        // Strip internal _source_path from export data
        foreach ($exportData['books'] as &$book) {
            foreach ($book['covers'] as &$cover) {
                unset($cover['_source_path']);
            }
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'leafmark_export_');

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create ZIP file');
        }

        $zip->addFromString('library.json', json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        foreach ($coverFiles as $zipPath => $storagePath) {
            $fullPath = Storage::disk('public')->path($storagePath);
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, $zipPath);
            }
        }

        $zip->close();

        return $tempFile;
    }

    public function buildExportData(): array
    {
        $tags = $this->collectTags();
        $books = $this->collectBooks($tags);
        $challenges = $this->collectChallenges();

        $totalCovers = 0;
        foreach ($books as $book) {
            $totalCovers += count(array_filter($book['covers'], fn($c) => $c['image_file'] !== null));
        }

        return [
            'schema_version' => 1,
            'exported_at' => now()->toIso8601String(),
            'app_version' => '1.0.0',
            'user' => [
                'name' => $this->user->name,
            ],
            'statistics' => [
                'total_books' => count($books),
                'total_tags' => count($tags),
                'total_covers' => $totalCovers,
                'total_challenges' => count($challenges),
            ],
            'tags' => $tags,
            'books' => $books,
            'reading_challenges' => $challenges,
        ];
    }

    private function collectTags(): array
    {
        $tags = $this->user->tags()->ordered()->get();
        $result = [];

        foreach ($tags as $index => $tag) {
            $result[] = [
                'ref_id' => 'tag_' . $index,
                'name' => $tag->name,
                'description' => $tag->description,
                'color' => $tag->color,
                'is_default' => $tag->is_default,
                'sort_order' => $tag->sort_order,
            ];
        }

        return $result;
    }

    private function collectBooks(array $tagsData): array
    {
        $books = $this->user->books()
            ->with(['tags', 'covers' => fn($q) => $q->ordered(), 'progressHistory'])
            ->orderBy('added_at')
            ->get();

        // Build tag name -> ref_id map
        $tagRefMap = [];
        foreach ($tagsData as $tagData) {
            $tagRefMap[$tagData['name']] = $tagData['ref_id'];
        }

        $result = [];

        foreach ($books as $bookIndex => $book) {
            $tagRefs = [];
            foreach ($book->tags as $tag) {
                if (isset($tagRefMap[$tag->name])) {
                    $tagRefs[] = $tagRefMap[$tag->name];
                }
            }

            $covers = [];
            foreach ($book->covers as $coverIndex => $cover) {
                $imageFile = null;
                $sourcePath = null;

                if ($cover->path && Storage::disk('public')->exists($cover->path)) {
                    $ext = pathinfo($cover->path, PATHINFO_EXTENSION) ?: 'jpg';
                    $imageFile = 'images/book_' . $bookIndex . '_cover_' . $coverIndex . '.' . $ext;
                    $sourcePath = $cover->path;
                }

                $covers[] = [
                    'image_file' => $imageFile,
                    'is_primary' => $cover->is_primary,
                    'sort_order' => $cover->sort_order,
                    '_source_path' => $sourcePath,
                ];
            }

            $progressHistory = [];
            foreach ($book->progressHistory as $entry) {
                $progressHistory[] = [
                    'page_number' => $entry->page_number,
                    'recorded_at' => $entry->recorded_at?->toIso8601String(),
                ];
            }

            $result[] = [
                'ref_id' => 'book_' . $bookIndex,
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn,
                'isbn13' => $book->isbn13,
                'publisher' => $book->publisher,
                'published_date' => $book->published_date?->format('Y-m-d'),
                'description' => $book->description,
                'page_count' => $book->page_count,
                'language' => $book->language,
                'current_page' => $book->current_page,
                'status' => $book->status,
                'format' => $book->format,
                'series' => $book->series,
                'series_position' => $book->series_position,
                'rating' => $book->rating ? (float) $book->rating : null,
                'review' => $book->review,
                'purchase_date' => $book->purchase_date?->format('Y-m-d'),
                'purchase_price' => $book->purchase_price ? (string) $book->purchase_price : null,
                'purchase_currency' => $book->purchase_currency,
                'added_at' => $book->added_at?->toIso8601String(),
                'started_at' => $book->started_at?->toIso8601String(),
                'finished_at' => $book->finished_at?->toIso8601String(),
                'cover_url' => $book->cover_url,
                'thumbnail' => $book->thumbnail,
                'api_source' => $book->api_source,
                'external_id' => $book->external_id,
                'openlibrary_edition_id' => $book->openlibrary_edition_id,
                'goodreads_id' => $book->goodreads_id,
                'librarything_id' => $book->librarything_id,
                'openlibrary_url' => $book->openlibrary_url,
                'tag_refs' => $tagRefs,
                'covers' => $covers,
                'progress_history' => $progressHistory,
            ];
        }

        return $result;
    }

    private function collectChallenges(): array
    {
        return $this->user->readingChallenges()
            ->orderBy('year')
            ->get()
            ->map(fn($c) => [
                'year' => $c->year,
                'goal' => $c->goal,
            ])
            ->toArray();
    }

    private function collectCoverFiles(array $exportData): array
    {
        $files = [];
        foreach ($exportData['books'] as $book) {
            foreach ($book['covers'] as $cover) {
                if ($cover['image_file'] && $cover['_source_path']) {
                    $files[$cover['image_file']] = $cover['_source_path'];
                }
            }
        }
        return $files;
    }
}
