<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookBrainzService
{
    private const SEARCH_URL = 'https://bookbrainz.org/search/search';

    /**
     * Search for books on BookBrainz
     *
     * @param string $query Search query (title, author, or ISBN)
     * @param int $limit Maximum number of results
     * @param string $language Preferred language (not used by BookBrainz currently)
     * @return array
     */
    public function search(string $query, int $limit = 10, string $language = 'en'): array
    {
        try {
            // BookBrainz uses a different search endpoint
            $response = Http::withHeaders([
                'User-Agent' => 'Leafmark/1.0 (https://github.com/roberteinsle/leafmark)',
                'Accept' => 'application/json',
            ])->get(self::SEARCH_URL, [
                'q' => $query,
                'type' => 'edition',
                'size' => $limit,
            ]);

            if (!$response->successful()) {
                Log::warning('BookBrainz API search failed', [
                    'status' => $response->status(),
                    'query' => $query,
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();

            // BookBrainz returns results in a 'results' array
            if (empty($data['results']) || !is_array($data['results'])) {
                return [];
            }

            return $this->formatSearchResults($data['results']);
        } catch (\Exception $e) {
            Log::error('BookBrainz search error', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);
            return [];
        }
    }

    /**
     * Get detailed book information by BookBrainz edition BBID
     *
     * @param string $bbid BookBrainz edition BBID
     * @return array|null
     */
    public function getBook(string $bbid): ?array
    {
        try {
            // Get edition details from the web page (BookBrainz doesn't have a public API endpoint)
            // For now, return null as BookBrainz requires scraping or MusicBrainz-style API access
            Log::info('BookBrainz getBook called', ['bbid' => $bbid]);
            return null;
        } catch (\Exception $e) {
            Log::error('BookBrainz getBook error', [
                'error' => $e->getMessage(),
                'bbid' => $bbid,
            ]);
            return null;
        }
    }

    /**
     * Format search results into standardized structure
     *
     * @param array $results
     * @return array
     */
    private function formatSearchResults(array $results): array
    {
        $formatted = [];

        foreach ($results as $result) {
            if (empty($result['bbid']) && empty($result['id'])) {
                continue;
            }

            $book = $this->formatBookData($result);
            if ($book) {
                $formatted[] = $book;
            }
        }

        return $formatted;
    }

    /**
     * Format book data into standardized structure
     *
     * @param array $result
     * @return array|null
     */
    private function formatBookData(array $result): ?array
    {
        $bbid = $result['bbid'] ?? null;

        if (!$bbid) {
            return null;
        }

        $title = $result['name'] ?? 'Unknown Title';

        // Extract ISBN from identifierSet
        $isbn = null;
        $isbn13 = null;
        if (!empty($result['identifierSet']['identifiers'])) {
            foreach ($result['identifierSet']['identifiers'] as $identifier) {
                // Type 10 is ISBN in BookBrainz
                if ($identifier['typeId'] === 10 && !empty($identifier['value'])) {
                    $isbnValue = $identifier['value'];
                    // Check if it's ISBN-13 (starts with 978 or 979 and has 13 digits)
                    if (preg_match('/^(978|979)/', str_replace('-', '', $isbnValue)) &&
                        strlen(str_replace('-', '', $isbnValue)) === 13) {
                        $isbn13 = $isbnValue;
                    } else {
                        $isbn = $isbnValue;
                    }
                }
            }
        }

        // Extract authors from relationshipSet
        $authors = [];
        if (!empty($result['relationshipSet']['relationships'])) {
            foreach ($result['relationshipSet']['relationships'] as $relationship) {
                // Check if this is an author relationship
                if (!empty($relationship['type']['label']) &&
                    (stripos($relationship['type']['label'], 'author') !== false ||
                     stripos($relationship['type']['label'], 'writer') !== false)) {
                    // Get the author name from the target entity
                    if (!empty($relationship['target']['name'])) {
                        $authors[] = $relationship['target']['name'];
                    }
                }
            }
        }

        // Extract publishers from relationshipSet
        $publishers = [];
        if (!empty($result['relationshipSet']['relationships'])) {
            foreach ($result['relationshipSet']['relationships'] as $relationship) {
                if (!empty($relationship['type']['label']) &&
                    stripos($relationship['type']['label'], 'publisher') !== false) {
                    if (!empty($relationship['target']['name'])) {
                        $publishers[] = $relationship['target']['name'];
                    }
                }
            }
        }

        // Get description from annotation if available
        $description = null;
        if (!empty($result['annotation']['content'])) {
            $description = $result['annotation']['content'];
        }

        return [
            'title' => $title,
            'author' => !empty($authors) ? implode(', ', $authors) : null,
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'publisher' => !empty($publishers) ? implode(', ', $publishers) : null,
            'published_date' => null, // BookBrainz has releaseEventSet but it's complex
            'description' => $description,
            'page_count' => $result['pages'] ?? null,
            'language' => null,
            'cover_image' => null,
            'thumbnail_image' => null,
            'thumbnail' => null,
            'bookbrainz_id' => $bbid,
            'external_id' => $bbid,
            'api_source' => 'bookbrainz',
        ];
    }

    /**
     * Extract BookBrainz edition BBID from URL
     *
     * @param string $url BookBrainz URL
     * @return string|null
     */
    public function extractBbidFromUrl(string $url): ?string
    {
        // Match patterns like:
        // https://bookbrainz.org/edition/BBID
        // BBID format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
        if (preg_match('/edition\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i', $url, $matches)) {
            return $matches[1];
        }

        // If it's already a BBID format, return it
        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $url)) {
            return $url;
        }

        return null;
    }
}
