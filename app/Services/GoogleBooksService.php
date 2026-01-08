<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBooksService
{
    private string $baseUrl = 'https://www.googleapis.com/books/v1';
    private ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_books.api_key');
    }

    /**
     * Search for books by query
     */
    public function search(string $query, int $maxResults = 10, ?string $language = null): array
    {
        try {
            // Parse special search prefixes
            $searchQuery = $this->parseSearchQuery($query);

            $params = [
                'q' => $searchQuery,
                'maxResults' => $maxResults,
            ];

            // Add language restriction if provided
            if ($language) {
                $params['langRestrict'] = $language;
            }

            if ($this->apiKey) {
                $params['key'] = $this->apiKey;
            }

            $userEmail = auth()->user()->email ?? 'leafmark@example.com';

            $response = Http::withHeaders([
                'User-Agent' => "Leafmark/1.0 ({$userEmail})",
            ])->get("{$this->baseUrl}/volumes", $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatResults($data['items'] ?? [], $language);
            }

            Log::error('Google Books API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Google Books API exception', [
                'message' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Parse search query for special prefixes
     */
    private function parseSearchQuery(string $query): string
    {
        // Check for ISBN prefix
        if (str_starts_with(strtolower($query), 'isbn:')) {
            $isbn = substr($query, 5);
            return 'isbn:' . preg_replace('/[^0-9X]/', '', strtoupper(trim($isbn)));
        }

        // Check for author prefix
        if (str_starts_with(strtolower($query), 'author:')) {
            $author = substr($query, 7);
            return 'inauthor:' . trim($author);
        }

        // Check for series prefix
        if (str_starts_with(strtolower($query), 'series:')) {
            $series = substr($query, 7);
            return 'intitle:' . trim($series);
        }

        return $query;
    }

    /**
     * Search by ISBN
     */
    public function searchByIsbn(string $isbn, ?string $language = null): array
    {
        $cleanIsbn = preg_replace('/[^0-9X]/', '', strtoupper($isbn));
        return $this->search("isbn:{$cleanIsbn}", 1, $language);
    }

    /**
     * Get book details by Google Books ID
     */
    public function getBook(string $id): ?array
    {
        try {
            $params = [];
            if ($this->apiKey) {
                $params['key'] = $this->apiKey;
            }

            $userEmail = auth()->user()->email ?? 'leafmark@example.com';

            $response = Http::withHeaders([
                'User-Agent' => "Leafmark/1.0 ({$userEmail})",
            ])->get("{$this->baseUrl}/volumes/{$id}", $params);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatBook($data);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Books API exception', [
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
        $volumeInfo = $item['volumeInfo'] ?? [];
        $industryIdentifiers = $volumeInfo['industryIdentifiers'] ?? [];

        $isbn = null;
        $isbn13 = null;

        foreach ($industryIdentifiers as $identifier) {
            if ($identifier['type'] === 'ISBN_13') {
                $isbn13 = $identifier['identifier'];
            } elseif ($identifier['type'] === 'ISBN_10') {
                $isbn = $identifier['identifier'];
            }
        }

        return [
            'google_books_id' => $item['id'] ?? null,
            'title' => $volumeInfo['title'] ?? 'Unknown Title',
            'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : null,
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'publisher' => $volumeInfo['publisher'] ?? null,
            'published_date' => $this->formatDate($volumeInfo['publishedDate'] ?? null),
            'description' => $volumeInfo['description'] ?? null,
            'page_count' => $volumeInfo['pageCount'] ?? null,
            'language' => $volumeInfo['language'] ?? null,
            'cover_url' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
            'thumbnail' => $volumeInfo['imageLinks']['smallThumbnail'] ?? null,
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

        // Handle different date formats from Google Books
        if (preg_match('/^\d{4}$/', $date)) {
            return $date . '-01-01';
        }

        if (preg_match('/^\d{4}-\d{2}$/', $date)) {
            return $date . '-01';
        }

        return $date;
    }
}
