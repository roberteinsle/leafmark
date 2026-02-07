<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ThaliaScraperService
{
    /**
     * Extract article ID from Thalia URL
     */
    public function extractArticleIdFromUrl(string $url): ?string
    {
        // Pattern: /artikeldetails/A1234567890 or artikeldetails/ID12345
        if (preg_match('/artikeldetails\/([A-Z]?\d+)/i', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Scrape book data from Thalia URL
     * Note: Thalia uses Cloudflare protection which may block automated requests
     */
    public function scrapeFromUrl(string $url): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
            ])
            ->timeout(15)
            ->get($url);

            // Check for Cloudflare challenge
            if ($response->status() === 403 || strpos($response->body(), 'Just a moment') !== false) {
                Log::warning('Thalia scrape blocked by Cloudflare', ['url' => $url]);
                return ['error' => 'cloudflare'];
            }

            if (!$response->successful()) {
                Log::warning('Thalia scrape failed', [
                    'url' => $url,
                    'status' => $response->status()
                ]);
                return null;
            }

            $html = $response->body();
            return $this->parseHtml($html, $url);
        } catch (\Exception $e) {
            Log::error('Thalia scrape exception', [
                'url' => $url,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Parse HTML to extract book data
     */
    private function parseHtml(string $html, string $url): ?array
    {
        $data = [
            'source' => 'thalia',
            'url' => $url,
        ];

        // Try to find JSON-LD structured data first (most reliable)
        if (preg_match('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/is', $html, $match)) {
            $jsonLd = json_decode($match[1], true);
            if ($jsonLd && isset($jsonLd['@type']) && $jsonLd['@type'] === 'Book') {
                $data['title'] = $jsonLd['name'] ?? null;
                $data['author'] = $jsonLd['author']['name'] ?? ($jsonLd['author'][0]['name'] ?? null);
                $data['isbn13'] = $jsonLd['isbn'] ?? null;
                $data['description'] = $jsonLd['description'] ?? null;
                $data['thumbnail'] = $jsonLd['image'] ?? null;
                $data['publisher'] = $jsonLd['publisher']['name'] ?? null;
            }
        }

        // Extract title from meta or HTML
        if (empty($data['title'])) {
            if (preg_match('/<meta\s+property="og:title"\s+content="([^"]+)"/i', $html, $match)) {
                $data['title'] = trim(html_entity_decode($match[1]));
                // Remove "- Thalia" suffix
                $data['title'] = preg_replace('/\s*[-|]\s*Thalia$/i', '', $data['title']);
            } elseif (preg_match('/<h1[^>]*class="[^"]*product-title[^"]*"[^>]*>([^<]+)/i', $html, $match)) {
                $data['title'] = trim(html_entity_decode($match[1]));
            }
        }

        // Extract author
        if (empty($data['author'])) {
            if (preg_match('/class="[^"]*product-author[^"]*"[^>]*>.*?<a[^>]*>([^<]+)/is', $html, $match)) {
                $data['author'] = trim(html_entity_decode($match[1]));
            }
        }

        // Extract cover image from og:image
        if (empty($data['thumbnail'])) {
            if (preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/i', $html, $match)) {
                $data['thumbnail'] = $match[1];
            }
        }

        // Extract ISBN from page
        if (empty($data['isbn13'])) {
            if (preg_match('/ISBN[:\s]*(\d{13})/i', $html, $match)) {
                $data['isbn13'] = $match[1];
            } elseif (preg_match('/EAN[:\s]*(\d{13})/i', $html, $match)) {
                $data['isbn13'] = $match[1];
            }
        }

        // Extract price (if available)
        if (preg_match('/class="[^"]*price[^"]*"[^>]*>([0-9,.]+)\s*€/i', $html, $match)) {
            // Just for information, not stored
        }

        // Only return if we got at least a title
        if (empty($data['title'])) {
            Log::warning('Thalia scrape: no title found', ['url' => $url]);
            return null;
        }

        return $data;
    }

    /**
     * Format result for display
     */
    public function formatForSearch(array $data): array
    {
        return [
            'source' => 'thalia',
            'thalia_url' => $data['url'] ?? null,
            'title' => $data['title'] ?? '',
            'author' => $data['author'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'isbn13' => $data['isbn13'] ?? null,
            'publisher' => $data['publisher'] ?? null,
            'published_date' => $data['published_date'] ?? null,
            'page_count' => $data['page_count'] ?? null,
            'description' => $data['description'] ?? null,
            'language' => $data['language'] ?? 'de',
            'thumbnail' => $data['thumbnail'] ?? null,
        ];
    }
}
