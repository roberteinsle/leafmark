<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BigBookApiService
{
    protected ?string $apiKey;
    protected string $baseUrl = 'https://api.bigbookapi.com';

    public function __construct()
    {
        // Use global API key from system settings
        $this->apiKey = SystemSetting::get('bigbook_api_key');
    }

    /**
     * Search for books - automatically detects ISBN, title, or author
     */
    public function search(string $query, int $limit = 10, ?string $language = null): array
    {
        if (!$this->apiKey) {
            Log::warning('Big Book API key not configured');
            return [];
        }

        try {
            // Build search parameters
            $params = [
                'api-key' => $this->apiKey,
                'number' => $limit,
            ];

            // Detect query type and add appropriate parameter
            $cleanQuery = str_replace(['-', ' '], '', $query);

            // Check if it's an ISBN (10 or 13 digits)
            if (preg_match('/^\d{10}$|^\d{13}$/', $cleanQuery)) {
                $params['isbn'] = $cleanQuery;
            } else {
                // General query search (handles both title and author)
                $params['query'] = $query;
            }

            // Note: Big Book API does not support language filtering in search
            // Language preference is ignored for this provider

            $response = Http::timeout(10)->get("{$this->baseUrl}/search-books", $params);

            if ($response->successful()) {
                $data = $response->json();
                $books = $data['books'] ?? [];

                // Big Book API returns books as nested arrays: [[book1], [book2], ...]
                // We need to flatten this structure
                $flattenedBooks = [];
                foreach ($books as $bookWrapper) {
                    if (is_array($bookWrapper) && !empty($bookWrapper)) {
                        $flattenedBooks[] = $bookWrapper[0];
                    }
                }

                return $this->formatResults($flattenedBooks);
            }

            // Handle rate limiting
            if ($response->status() === 429) {
                Log::warning('Big Book API rate limit exceeded', [
                    'headers' => $response->headers(),
                ]);
                return [];
            }

            Log::error('Big Book API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Big Book API exception', [
                'message' => $e->getMessage(),
                'query' => $query,
            ]);

            return [];
        }
    }

    /**
     * Get a single book by ID
     */
    public function getBook(string $bigBookId): ?array
    {
        if (!$this->apiKey) {
            Log::warning('Big Book API key not configured');
            return null;
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$bigBookId}", [
                'api-key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatBook($data);
            }

            Log::error('Big Book API error', [
                'status' => $response->status(),
                'id' => $bigBookId,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Big Book API exception', [
                'message' => $e->getMessage(),
                'id' => $bigBookId,
            ]);

            return null;
        }
    }

    /**
     * Format search results to consistent structure
     */
    protected function formatResults(array $books): array
    {
        return array_map(function ($book) {
            return $this->formatBook($book);
        }, $books);
    }

    /**
     * Format a single book item to match our standard structure
     */
    protected function formatBook(array $book): array
    {
        // Extract authors - Big Book API returns array of author objects
        $authors = [];
        if (isset($book['authors']) && is_array($book['authors'])) {
            foreach ($book['authors'] as $author) {
                if (is_string($author)) {
                    $authors[] = $author;
                } elseif (isset($author['name'])) {
                    $authors[] = $author['name'];
                }
            }
        }

        // Extract ISBN from identifiers
        $isbn = null;
        $isbn13 = null;
        if (isset($book['isbn'])) {
            $cleanIsbn = str_replace(['-', ' '], '', $book['isbn']);
            if (strlen($cleanIsbn) === 10) {
                $isbn = $cleanIsbn;
            } elseif (strlen($cleanIsbn) === 13) {
                $isbn13 = $cleanIsbn;
            }
        }
        if (isset($book['isbn13'])) {
            $isbn13 = str_replace(['-', ' '], '', $book['isbn13']);
        }

        // Get cover image - Big Book API provides various sizes
        $coverUrl = $book['image'] ?? $book['image_url'] ?? null;
        $thumbnail = $book['thumbnail'] ?? $coverUrl;

        // Extract language (Big Book API uses ISO 639-1 codes like 'en', 'de')
        $language = $book['language'] ?? $book['language_code'] ?? 'en';

        return [
            'bigbook_id' => $book['id'] ?? null,
            'title' => $book['title'] ?? 'Unknown Title',
            'subtitle' => $book['subtitle'] ?? null,
            'author' => !empty($authors) ? implode(', ', $authors) : null,
            'publisher' => $book['publisher'] ?? null,
            'published_date' => $this->formatDate($book['publish_date'] ?? $book['published_date'] ?? null),
            'description' => $book['description'] ?? null,
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'page_count' => $book['pages'] ?? $book['page_count'] ?? null,
            'categories' => isset($book['genres']) ? (is_array($book['genres']) ? $book['genres'] : [$book['genres']]) : [],
            'language' => $language,
            'thumbnail' => $thumbnail,
            'cover_url' => $coverUrl,
        ];
    }

    /**
     * Format date to Y-m-d format
     */
    protected function formatDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        // Handle year-only format
        if (preg_match('/^\d{4}$/', $date)) {
            return $date . '-01-01';
        }

        // Try to parse date
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return $date;
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
