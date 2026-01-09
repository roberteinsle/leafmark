<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBooksService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://www.googleapis.com/books/v1';

    public function __construct()
    {
        $this->apiKey = config('services.google_books.api_key');
    }

    /**
     * Search for books - automatically detects ISBN, title, or author
     */
    public function search(string $query, int $maxResults = 20, string $language = null): array
    {
        // Detect query type and build appropriate search query
        $searchQuery = $this->buildSearchQuery($query);

        // Add language filter if specified
        if ($language) {
            $searchQuery .= '&langRestrict=' . $language;
        }

        try {
            $response = Http::get("{$this->baseUrl}/volumes", [
                'q' => $searchQuery,
                'key' => $this->apiKey,
                'maxResults' => $maxResults,
                'orderBy' => 'relevance',
            ]);

            if ($response->successful()) {
                return $this->formatResults($response->json());
            }

            Log::error('Google Books API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Google Books API exception', [
                'message' => $e->getMessage(),
                'query' => $searchQuery,
            ]);

            return [];
        }
    }

    /**
     * Get a single book by ID
     */
    public function getBook(string $googleBooksId): ?array
    {
        try {
            $response = Http::get("{$this->baseUrl}/volumes/{$googleBooksId}", [
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatBook($data);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Books API exception', [
                'message' => $e->getMessage(),
                'id' => $googleBooksId,
            ]);

            return null;
        }
    }

    /**
     * Detect query type and build appropriate search string
     */
    protected function buildSearchQuery(string $query): string
    {
        $query = trim($query);

        // ISBN-10 or ISBN-13 (with or without hyphens)
        if (preg_match('/^(\d{10}|\d{13}|\d{1,5}-\d{1,7}-\d{1,7}-[\dX])$/i', str_replace('-', '', $query))) {
            return 'isbn:' . str_replace('-', '', $query);
        }

        // Check if query looks like an author name (2+ words with capitalization)
        if (preg_match('/^[A-Z][a-z]+\s+[A-Z][a-z]+/', $query)) {
            return 'inauthor:' . $query;
        }

        // Default to title search
        return 'intitle:' . $query;
    }

    /**
     * Format API response to consistent structure
     */
    protected function formatResults(array $data): array
    {
        if (!isset($data['items'])) {
            return [];
        }

        return array_map(function ($item) {
            return $this->formatBook($item);
        }, $data['items']);
    }

    /**
     * Format a single book item
     */
    protected function formatBook(array $item): array
    {
        $volumeInfo = $item['volumeInfo'] ?? [];
        $industryIdentifiers = $volumeInfo['industryIdentifiers'] ?? [];

        // Extract ISBNs
        $isbn = null;
        $isbn13 = null;
        foreach ($industryIdentifiers as $identifier) {
            if ($identifier['type'] === 'ISBN_10') {
                $isbn = $identifier['identifier'];
            } elseif ($identifier['type'] === 'ISBN_13') {
                $isbn13 = $identifier['identifier'];
            }
        }

        $authors = $volumeInfo['authors'] ?? [];

        return [
            'google_books_id' => $item['id'] ?? null,
            'title' => $volumeInfo['title'] ?? 'Unknown Title',
            'subtitle' => $volumeInfo['subtitle'] ?? null,
            'author' => !empty($authors) ? implode(', ', $authors) : null, // String for compatibility
            'publisher' => $volumeInfo['publisher'] ?? null,
            'published_date' => $volumeInfo['publishedDate'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'page_count' => $volumeInfo['pageCount'] ?? null,
            'categories' => $volumeInfo['categories'] ?? [],
            'language' => $volumeInfo['language'] ?? 'en',
            'thumbnail' => $volumeInfo['imageLinks']['thumbnail'] ?? null, // Renamed for compatibility
            'cover_url' => $volumeInfo['imageLinks']['medium'] ?? $volumeInfo['imageLinks']['large'] ?? null,
            'preview_link' => $volumeInfo['previewLink'] ?? null,
            'info_link' => $volumeInfo['infoLink'] ?? null,
        ];
    }
}
