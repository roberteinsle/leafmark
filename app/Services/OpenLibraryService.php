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

            // Add language preference (ISO 639-1 codes: en, de, fr, etc.)
            // This influences but doesn't exclude results
            if ($language) {
                $params['lang'] = $language;
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
     * Get book details by Open Library Work ID
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
     * Get edition details from OpenLibrary URL or Edition ID
     * Supports URLs like: https://openlibrary.org/books/OL9064566M/...
     * Or direct edition IDs like: OL9064566M
     */
    public function getEdition(string $urlOrId): ?array
    {
        try {
            // Extract edition ID from URL if needed
            $editionId = $urlOrId;
            if (str_contains($urlOrId, 'openlibrary.org')) {
                // Extract ID from URL: https://openlibrary.org/books/OL9064566M/...
                preg_match('/\/books\/(OL\d+M)/', $urlOrId, $matches);
                if (!empty($matches[1])) {
                    $editionId = $matches[1];
                } else {
                    return null;
                }
            }

            $userEmail = auth()->user()->email ?? 'leafmark@example.com';

            $response = Http::withHeaders([
                'User-Agent' => "Leafmark/1.0 ({$userEmail})",
            ])->get("{$this->baseUrl}/books/{$editionId}.json");

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            // Extract identifiers from the edition
            $identifiers = $data['identifiers'] ?? [];
            $isbn = null;
            $isbn13 = null;

            // Extract ISBNs
            if (isset($identifiers['isbn_10'])) {
                $isbn = is_array($identifiers['isbn_10']) ? $identifiers['isbn_10'][0] : $identifiers['isbn_10'];
            }
            if (isset($identifiers['isbn_13'])) {
                $isbn13 = is_array($identifiers['isbn_13']) ? $identifiers['isbn_13'][0] : $identifiers['isbn_13'];
            }

            // Extract other identifiers
            $goodreadsId = null;
            if (isset($identifiers['goodreads'])) {
                $goodreadsId = is_array($identifiers['goodreads']) ? $identifiers['goodreads'][0] : $identifiers['goodreads'];
            }

            $librarythingId = null;
            if (isset($identifiers['librarything'])) {
                $librarythingId = is_array($identifiers['librarything']) ? $identifiers['librarything'][0] : $identifiers['librarything'];
            }

            // Get cover
            $coverIds = $data['covers'] ?? [];
            $coverId = !empty($coverIds) ? $coverIds[0] : null;
            $coverUrl = $coverId ? "https://covers.openlibrary.org/b/id/{$coverId}-L.jpg" : null;

            return [
                'openlibrary_edition_id' => $editionId,
                'openlibrary_url' => "https://openlibrary.org/books/{$editionId}",
                'title' => $data['title'] ?? null,
                'author' => isset($data['authors']) ? $this->extractAuthors($data['authors']) : null,
                'isbn' => $isbn,
                'isbn13' => $isbn13,
                'publisher' => isset($data['publishers']) ? (is_array($data['publishers']) ? $data['publishers'][0] : $data['publishers']) : null,
                'published_date' => $data['publish_date'] ?? null,
                'page_count' => $data['number_of_pages'] ?? null,
                'language' => isset($data['languages']) ? $this->extractLanguage($data['languages']) : null,
                'cover_url' => $coverUrl,
                'thumbnail' => $coverUrl,
                'goodreads_id' => $goodreadsId,
                'librarything_id' => $librarythingId,
            ];
        } catch (\Exception $e) {
            Log::error('Open Library Edition API exception', [
                'message' => $e->getMessage(),
                'url' => $urlOrId
            ]);
            return null;
        }
    }

    /**
     * Extract authors from edition data
     */
    private function extractAuthors(array $authors): ?string
    {
        $authorNames = [];
        foreach ($authors as $author) {
            if (isset($author['key'])) {
                // Fetch author name from key
                try {
                    $response = Http::get($this->baseUrl . $author['key'] . '.json');
                    if ($response->successful()) {
                        $authorData = $response->json();
                        $authorNames[] = $authorData['name'] ?? 'Unknown';
                    }
                } catch (\Exception $e) {
                    // Skip this author
                }
            }
        }
        return !empty($authorNames) ? implode(', ', $authorNames) : null;
    }

    /**
     * Extract language from edition data
     */
    private function extractLanguage(array $languages): ?string
    {
        if (empty($languages)) {
            return null;
        }

        $lang = is_array($languages[0]) ? ($languages[0]['key'] ?? null) : $languages[0];
        if ($lang && str_contains($lang, '/languages/')) {
            // Extract language code from key like "/languages/ger"
            $lang = str_replace('/languages/', '', $lang);
        }

        return $lang;
    }

    /**
     * Format search results
     */
    private function formatResults(array $items, ?string $filterLanguage = null): array
    {
        $formatted = [];

        // Map ISO 639-1 to ISO 639-2 for matching
        $langMap = [
            'en' => 'eng',
            'de' => 'ger',
            'fr' => 'fre',
            'es' => 'spa',
            'it' => 'ita',
            'pt' => 'por',
            'nl' => 'dut',
            'ru' => 'rus',
            'ja' => 'jpn',
            'zh' => 'chi',
        ];

        $preferredLang = $filterLanguage ? ($langMap[$filterLanguage] ?? $filterLanguage) : null;

        foreach ($items as $item) {
            $book = $this->formatBook($item);
            $bookLangs = $book['language'] ?? null;

            // Convert language array to string for storage
            if (is_array($bookLangs)) {
                // If we have a preferred language and it exists in the array, use it
                if ($preferredLang && in_array($preferredLang, $bookLangs)) {
                    $book['language'] = $filterLanguage; // Store as ISO 639-1 (e.g., 'de')
                } else {
                    // Otherwise use the first language in the array
                    $book['language'] = $bookLangs[0] ?? null;
                }
            }

            // Try to select a language-specific ISBN if available
            if ($filterLanguage === 'de' && !empty($book['all_isbns'])) {
                // For German, prefer ISBNs starting with 3
                $germanIsbns = array_filter(
                    $book['all_isbns']['isbn13'] ?? [],
                    fn($isbn) => str_starts_with($isbn, '3') || str_starts_with($isbn, '978' . '3')
                );
                if (!empty($germanIsbns)) {
                    $book['isbn13'] = reset($germanIsbns);
                }

                $germanIsbns10 = array_filter(
                    $book['all_isbns']['isbn'] ?? [],
                    fn($isbn) => str_starts_with($isbn, '3')
                );
                if (!empty($germanIsbns10)) {
                    $book['isbn'] = reset($germanIsbns10);
                }
            }

            // Remove temporary data
            unset($book['all_isbns']);

            $formatted[] = $book;
        }

        return $formatted;
    }

    /**
     * Format a single book from API response
     */
    private function formatBook(array $item): array
    {
        // Extract ISBNs - take the first ones found
        $isbn = null;
        $isbn13 = null;
        $allIsbns = []; // Store all ISBNs for later selection

        if (isset($item['isbn'])) {
            foreach ($item['isbn'] as $isbnValue) {
                $cleanIsbn = str_replace(['-', ' '], '', $isbnValue);
                if (strlen($cleanIsbn) === 13) {
                    $allIsbns['isbn13'][] = $cleanIsbn;
                    if (!$isbn13) $isbn13 = $cleanIsbn; // First ISBN-13
                } elseif (strlen($cleanIsbn) === 10) {
                    $allIsbns['isbn'][] = $cleanIsbn;
                    if (!$isbn) $isbn = $cleanIsbn; // First ISBN-10
                }
            }
        }

        // Extract Open Library ID from key
        $openLibraryId = null;
        if (isset($item['key'])) {
            $openLibraryId = str_replace('/works/', '', $item['key']);
        }

        // Get cover image URL - use cover_i if available, otherwise try ISBN
        $coverId = $item['cover_i'] ?? null;
        $coverUrl = null;
        $thumbnail = null;

        if ($coverId) {
            // Use OpenLibrary cover ID
            $coverUrl = "https://covers.openlibrary.org/b/id/{$coverId}-L.jpg";
            $thumbnail = "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg";
        } elseif ($isbn13 || $isbn) {
            // Fallback: Try ISBN-based cover
            $coverIsbn = $isbn13 ?? $isbn;
            $coverUrl = "https://covers.openlibrary.org/b/isbn/{$coverIsbn}-L.jpg";
            $thumbnail = "https://covers.openlibrary.org/b/isbn/{$coverIsbn}-M.jpg";
        }

        return [
            'open_library_id' => $openLibraryId,
            'title' => $item['title'] ?? 'Unknown Title',
            'author' => isset($item['author_name']) ? implode(', ', $item['author_name']) : null,
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'all_isbns' => $allIsbns, // Store all ISBNs for language-specific selection
            'publisher' => isset($item['publisher']) ? $item['publisher'][0] ?? null : null,
            'published_date' => isset($item['publish_date']) ? $this->formatDate($item['publish_date'][0] ?? null) : null,
            'description' => isset($item['first_sentence']) ? (is_array($item['first_sentence']) ? implode(' ', $item['first_sentence']) : $item['first_sentence']) : null,
            'page_count' => $item['number_of_pages'] ?? null,
            'language' => $item['language'] ?? null, // Keep as array for filtering
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

        // Default to general search (no prefix) - let OpenLibrary's search decide
        // This gives better results than forcing 'title:' prefix
        return $query;
    }
}
