<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenLibraryService
{
    private string $baseUrl = 'https://openlibrary.org';

    /**
     * Search for books by query - automatically detects ISBN, title, or author
     */
    public function search(string $query, int $limit = 10, ?string $language = null): array
    {
        // Detect query type and build appropriate search query
        $searchQuery = $this->buildSearchQuery($query);

        try {
            $params = [
                'q' => $searchQuery,
                'limit' => $limit,
                'fields' => 'key,title,author_name,isbn,publisher,publish_date,number_of_pages,language,cover_i,first_sentence',
            ];

            // Add language filter if provided
            if ($language) {
                $params['language'] = $language;
            }

            // Get user email for User-Agent
            $userEmail = auth()->user()->email ?? 'leafmark@example.com';

            $response = Http::withHeaders([
                'User-Agent' => "Leafmark/1.0 ({$userEmail})",
            ])->get("{$this->baseUrl}/search.json", $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatResults($data['docs'] ?? [], $language);
            }

            Log::error('Open Library API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Open Library API exception', [
                'message' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get book details by Open Library ID
     */
    public function getBook(string $id): ?array
    {
        try {
            $userEmail = auth()->user()->email ?? 'leafmark@example.com';

            $response = Http::withHeaders([
                'User-Agent' => "Leafmark/1.0 ({$userEmail})",
            ])->get("{$this->baseUrl}/works/{$id}.json");

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatBook($data);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Open Library API exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Format search results
     */
    private function formatResults(array $items, ?string $filterLanguage = null): array
    {
        $formatted = array_map(fn($item) => $this->formatBook($item), $items);

        // If language filter is specified, post-filter results
        if ($filterLanguage) {
            $formatted = array_filter($formatted, function($book) use ($filterLanguage) {
                $bookLang = $book['language'] ?? null;
                // Accept if no language specified or if it matches
                return !$bookLang || $bookLang === $filterLanguage;
            });

            // Re-index array after filtering
            $formatted = array_values($formatted);
        }

        return $formatted;
    }

    /**
     * Format a single book from API response
     */
    private function formatBook(array $item): array
    {
        // Extract ISBN (prefer ISBN-13 over ISBN-10)
        $isbn = null;
        $isbn13 = null;

        if (isset($item['isbn'])) {
            foreach ($item['isbn'] as $isbnValue) {
                if (strlen($isbnValue) === 13) {
                    $isbn13 = $isbnValue;
                } elseif (strlen($isbnValue) === 10) {
                    $isbn = $isbnValue;
                }
            }
        }

        // Extract Open Library ID from key
        $openLibraryId = null;
        if (isset($item['key'])) {
            $openLibraryId = str_replace('/works/', '', $item['key']);
        }

        // Get cover image URL
        $coverId = $item['cover_i'] ?? null;
        $coverUrl = $coverId ? "https://covers.openlibrary.org/b/id/{$coverId}-L.jpg" : null;
        $thumbnail = $coverId ? "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg" : null;

        return [
            'open_library_id' => $openLibraryId,
            'title' => $item['title'] ?? 'Unknown Title',
            'author' => isset($item['author_name']) ? implode(', ', $item['author_name']) : null,
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'publisher' => isset($item['publisher']) ? $item['publisher'][0] ?? null : null,
            'published_date' => isset($item['publish_date']) ? $this->formatDate($item['publish_date'][0] ?? null) : null,
            'description' => isset($item['first_sentence']) ? (is_array($item['first_sentence']) ? implode(' ', $item['first_sentence']) : $item['first_sentence']) : null,
            'page_count' => $item['number_of_pages'] ?? null,
            'language' => isset($item['language']) ? $item['language'][0] ?? null : null,
            'cover_url' => $coverUrl,
            'thumbnail' => $thumbnail,
        ];
    }

    /**
     * Format date from various formats to Y-m-d
     */
    private function formatDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        // Handle different date formats
        if (preg_match('/^\d{4}$/', $date)) {
            return $date . '-01-01';
        }

        if (preg_match('/^(\w+)\s+(\d+),\s+(\d{4})$/', $date, $matches)) {
            // Format: "January 1, 2000"
            return date('Y-m-d', strtotime($date));
        }

        // Try to parse any other format
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return $date;
    }

    /**
     * Detect query type and build appropriate search string
     */
    protected function buildSearchQuery(string $query): string
    {
        $query = trim($query);

        // Remove 'isbn:', 'author:', 'title:' prefixes if user added them manually
}
        // Remove 'isbn:', 'author:', 'title:' prefixes if user added them manually
        if (preg_match('/^(isbn|author|title):/i', $query)) {
            return $query; // Already formatted, return as-is
        }

        // Clean query for ISBN check (remove hyphens and spaces)
        $cleanQuery = str_replace(['-', ' '], '', $query);

        // ISBN-10 (10 digits) or ISBN-13 (13 digits)
        if (preg_match('/^\d{10}$|^\d{13}$/', $cleanQuery)) {
            return 'isbn:' . $cleanQuery;
        }

        // Check if query looks like an author name (2+ words, first letters capitalized)
        if (preg_match('/^[A-Z][a-z]+\s+[A-Z]/', $query)) {
            return 'author:' . $query;
        }

        // Default to title search
        return 'title:' . $query;
    }
}
