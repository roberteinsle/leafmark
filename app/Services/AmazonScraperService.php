<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AmazonScraperService
{
    /**
     * Extract ISBN/ASIN from Amazon URL
     */
    public function extractIsbnFromUrl(string $url): ?string
    {
        $patterns = [
            '/\/dp\/(\d{10}|\d{13})/i',
            '/\/gp\/product\/(\d{10}|\d{13})/i',
            '/\/product\/(\d{10}|\d{13})/i',
            '/\/(\d{10})(?:\/|$|\?)/i',
            '/\/(\d{13})(?:\/|$|\?)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Scrape book data from Amazon URL
     */
    public function scrapeFromUrl(string $url): ?array
    {
        try {
            // Note: Minimal headers to avoid bot detection
            // Adding extra headers like Accept-Encoding, Cache-Control triggers CAPTCHA
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
            ])
            ->timeout(15)
            ->get($url);

            if (!$response->successful()) {
                Log::warning('Amazon scrape failed', [
                    'url' => $url,
                    'status' => $response->status()
                ]);
                return null;
            }

            $html = $response->body();
            return $this->parseHtml($html, $url);
        } catch (\Exception $e) {
            Log::error('Amazon scrape exception', [
                'url' => $url,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Search by ISBN (extracts from URL and scrapes)
     */
    public function searchByIsbn(string $isbn): ?array
    {
        // Try German Amazon first
        $url = "https://www.amazon.de/dp/{$isbn}";
        $result = $this->scrapeFromUrl($url);

        if (!$result) {
            // Fallback to .com
            $url = "https://www.amazon.com/dp/{$isbn}";
            $result = $this->scrapeFromUrl($url);
        }

        return $result;
    }

    /**
     * Parse HTML to extract book data
     */
    private function parseHtml(string $html, string $url): ?array
    {
        $data = [
            'source' => 'amazon',
            'url' => $url,
        ];

        // Extract title - try multiple sources
        // 1. Try productTitle span (dynamic content)
        if (preg_match('/<span[^>]*id="productTitle"[^>]*>([^<]+)<\/span>/i', $html, $match)) {
            $data['title'] = trim(html_entity_decode($match[1]));
        }
        // 2. Try meta title tag (most reliable for static HTML)
        // Note: Use separate patterns for double and single quotes to handle apostrophes in content
        elseif (preg_match('/<meta\s+name="title"\s+content="([^"]+)"/i', $html, $match) ||
                preg_match("/<meta\s+name='title'\s+content='([^']+)'/i", $html, $match)) {
            $titleContent = trim(html_entity_decode($match[1]));
            // Remove Amazon suffix first: ": Amazon.de: Bücher" or similar
            $titleContent = preg_replace('/:\s*Amazon\.[a-z]+:\s*Bücher$/i', '', $titleContent);
            $titleContent = preg_replace('/:\s*Amazon\.[a-z]+:\s*Books$/i', '', $titleContent);
            $titleContent = trim($titleContent);

            // Meta title often includes author: "Book Title : Author, Name..."
            if (strpos($titleContent, ' : ') !== false) {
                $parts = explode(' : ', $titleContent, 2);
                $data['title'] = trim($parts[0]);
                // Extract author from meta title if present
                if (!empty($parts[1])) {
                    $authorPart = trim($parts[1]);
                    // Remove trailing "..." or other suffixes
                    $authorPart = preg_replace('/\.{3,}.*$/', '', $authorPart);
                    // Take only the first author (before second comma pair)
                    // Format: "Lastname1, Firstname1, Lastname2, Firstname2..."
                    // We want "Firstname1 Lastname1"
                    if (preg_match('/^([^,]+),\s*([^,]+)/', $authorPart, $authorMatch)) {
                        $data['author'] = trim($authorMatch[2] . ' ' . $authorMatch[1]);
                    } else {
                        $data['author'] = trim($authorPart);
                    }
                }
            } else {
                $data['title'] = $titleContent;
            }
        }
        // 3. Try HTML title tag
        elseif (preg_match('/<title>([^<]+)<\/title>/i', $html, $match)) {
            $titleContent = trim(html_entity_decode($match[1]));
            // Remove Amazon suffix like "...Bücher" or ": Amazon.de"
            $titleContent = preg_replace('/\s*:\s*Amazon\.[a-z]+.*$/i', '', $titleContent);
            $titleContent = preg_replace('/Bücher$/', '', $titleContent);
            $titleContent = trim($titleContent);
            // Same parsing as meta title
            if (strpos($titleContent, ' : ') !== false) {
                $parts = explode(' : ', $titleContent, 2);
                $data['title'] = trim($parts[0]);
                if (empty($data['author']) && !empty($parts[1])) {
                    $authorPart = trim($parts[1]);
                    $authorPart = preg_replace('/\.{3,}.*$/', '', $authorPart);
                    if (strpos($authorPart, ', ') !== false) {
                        $nameParts = explode(', ', $authorPart, 2);
                        $data['author'] = trim($nameParts[1] . ' ' . $nameParts[0]);
                    } else {
                        $data['author'] = trim($authorPart);
                    }
                }
            } else {
                $data['title'] = $titleContent;
            }
        }
        // 4. Try h1 with size class
        elseif (preg_match('/<h1[^>]*class="[^"]*a-size-large[^"]*"[^>]*>([^<]+)<\/h1>/i', $html, $match)) {
            $data['title'] = trim(html_entity_decode($match[1]));
        }

        // Extract author if not already found from title
        if (empty($data['author'])) {
            if (preg_match('/<span[^>]*class="author[^"]*"[^>]*>.*?<a[^>]*>([^<]+)<\/a>/is', $html, $match)) {
                $data['author'] = trim(html_entity_decode($match[1]));
            } elseif (preg_match('/von\s+<a[^>]*>([^<]+)<\/a>/i', $html, $match)) {
                $data['author'] = trim(html_entity_decode($match[1]));
            }
        }

        // Extract cover image
        if (preg_match('/<img[^>]*id="imgBlkFront"[^>]*src="([^"]+)"/i', $html, $match)) {
            $data['thumbnail'] = $match[1];
        } elseif (preg_match('/<img[^>]*id="landingImage"[^>]*src="([^"]+)"/i', $html, $match)) {
            $data['thumbnail'] = $match[1];
        } elseif (preg_match('/<img[^>]*data-a-dynamic-image="\{&quot;([^&]+)&quot;/i', $html, $match)) {
            $data['thumbnail'] = html_entity_decode($match[1]);
        }
        // Try og:image meta tag
        elseif (preg_match('/<meta\s+property="og:image"\s+content="([^"]+)"/i', $html, $match) ||
                preg_match("/<meta\s+property='og:image'\s+content='([^']+)'/i", $html, $match)) {
            $data['thumbnail'] = $match[1];
        }

        // Extract ISBN from meta description (often contains "ISBN: 1234567890")
        if (preg_match('/<meta\s+name="description"\s+content="([^"]+)"/i', $html, $match) ||
            preg_match("/<meta\s+name='description'\s+content='([^']+)'/i", $html, $match)) {
            $description = $match[1];
            // Look for ISBN in description - capture all digits after ISBN
            if (preg_match('/ISBN[:\s]*(\d+)/i', $description, $isbnMatch)) {
                $isbn = $isbnMatch[1];
                if (strlen($isbn) === 13) {
                    $data['isbn13'] = $isbn;
                } elseif (strlen($isbn) === 10) {
                    $data['isbn'] = $isbn;
                }
                // Ignore if not 10 or 13 digits
            }
        }

        // Amazon detail-bullet structure:
        // <span class="a-text-bold">Label &rlm; : &lrm;</span> <span>Value</span>
        // Pattern to match the colon separator with whitespace and &rlm;/&lrm; entities
        $colonPart = '[\s\n]*&rlm;[\s\n]*:[\s\n]*&lrm;[\s\n]*';

        // Extract from product details section (detailBullets)
        $detailPatterns = [
            'isbn13' => [
                '/ISBN-13' . $colonPart . '<\/span>\s*<span[^>]*>\s*([\d-]+)/is',
            ],
            'isbn' => [
                '/ISBN-10' . $colonPart . '<\/span>\s*<span[^>]*>\s*([\dX-]+)/is',
            ],
            'publisher' => [
                '/Herausgeber' . $colonPart . '<\/span>\s*<span[^>]*>\s*([^<]+)/is',
                '/Publisher' . $colonPart . '<\/span>\s*<span[^>]*>\s*([^<]+)/is',
                '/Verlag' . $colonPart . '<\/span>\s*<span[^>]*>\s*([^<]+)/is',
            ],
            'published_date' => [
                '/Erscheinungstermin' . $colonPart . '<\/span>\s*<span[^>]*>\s*([^<]+)/is',
                '/Publication date' . $colonPart . '<\/span>\s*<span[^>]*>\s*([^<]+)/is',
            ],
            'page_count' => [
                '/Seitenzahl[^<]*' . $colonPart . '<\/span>\s*<span[^>]*>\s*(\d+)/is',
                '/(\d+)\s*Seiten/i',
                '/(\d+)\s*pages/i',
            ],
            'language' => [
                '/Sprache' . $colonPart . '<\/span>\s*<span[^>]*>\s*([^<]+)/is',
                '/Language' . $colonPart . '<\/span>\s*<span[^>]*>\s*([^<]+)/is',
            ],
        ];

        foreach ($detailPatterns as $field => $patterns) {
            // Skip if already extracted
            if (!empty($data[$field])) {
                continue;
            }
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $match)) {
                    $value = trim(html_entity_decode($match[1]));

                    if ($field === 'isbn13' || $field === 'isbn') {
                        $value = preg_replace('/[^0-9X]/i', '', $value);
                    } elseif ($field === 'page_count') {
                        $value = (int) $value;
                    } elseif ($field === 'language') {
                        $value = $this->normalizeLanguage($value);
                    } elseif ($field === 'published_date') {
                        $value = $this->parseDate($value);
                    }

                    if ($value) {
                        $data[$field] = $value;
                    }
                    break;
                }
            }
        }

        // Extract description
        if (preg_match('/<div[^>]*id="bookDescription[^"]*"[^>]*>(.*?)<\/div>/is', $html, $match)) {
            $data['description'] = trim(strip_tags($match[1]));
        } elseif (preg_match('/<div[^>]*data-a-expander-name="book_description"[^>]*>.*?<span[^>]*>(.*?)<\/span>/is', $html, $match)) {
            $data['description'] = trim(strip_tags($match[1]));
        }

        // Extract series information
        // Format: "Buch X von Y: Series Name" appears in multiple locations with different HTML
        // Note: Amazon uses non-breaking spaces (\xC2\xA0) between words
        $sp = '[\s\xA0]+';  // Space pattern including non-breaking space
        $seriesPatterns = [
            // Simple format: "Buch 4 von 6: Five Nights at Freddy's" (no &rlm;/&lrm;)
            '/Buch' . $sp . '(\d+)' . $sp . 'von' . $sp . '\d+:\s*([^<\n]+)/iu',
            '/Book' . $sp . '(\d+)' . $sp . 'of' . $sp . '\d+:\s*([^<\n]+)/iu',
            // Detail-bullets format with link: <span>Buch X von Y &rlm;:&lrm;</span> <a...><span>Series</span></a>
            '/Buch' . $sp . '(\d+)' . $sp . 'von' . $sp . '\d+' . $colonPart . '<\/span>\s*<a[^>]*>\s*<span[^>]*>\s*([^<]+)/isu',
            '/Book' . $sp . '(\d+)' . $sp . 'of' . $sp . '\d+' . $colonPart . '<\/span>\s*<a[^>]*>\s*<span[^>]*>\s*([^<]+)/isu',
            // Alternative patterns
            '/<a[^>]*id="seriesTitle[^"]*"[^>]*>([^<]+)<\/a>/i',
            '/series-page-link[^>]*>([^<]+)</i',
        ];

        foreach ($seriesPatterns as $pattern) {
            if (preg_match($pattern, $html, $seriesMatch)) {
                if (isset($seriesMatch[2])) {
                    // Pattern with position and name
                    $data['series'] = trim(html_entity_decode($seriesMatch[2]));
                    $data['series_position'] = (int) $seriesMatch[1];
                } else {
                    // Pattern with just name
                    $data['series'] = trim(html_entity_decode($seriesMatch[1]));
                }
                break;
            }
        }

        // Only return if we got at least a title
        if (empty($data['title'])) {
            Log::warning('Amazon scrape: no title found', ['url' => $url]);
            return null;
        }

        return $data;
    }

    /**
     * Normalize language string to ISO code
     */
    private function normalizeLanguage(string $language): string
    {
        $language = strtolower(trim($language));

        $map = [
            'deutsch' => 'de',
            'german' => 'de',
            'englisch' => 'en',
            'english' => 'en',
            'französisch' => 'fr',
            'french' => 'fr',
            'spanisch' => 'es',
            'spanish' => 'es',
            'italienisch' => 'it',
            'italian' => 'it',
        ];

        return $map[$language] ?? substr($language, 0, 2);
    }

    /**
     * Parse date string to Y-m-d format
     */
    private function parseDate(string $dateStr): ?string
    {
        $months = [
            'januar' => 1, 'january' => 1, 'jan' => 1,
            'februar' => 2, 'february' => 2, 'feb' => 2,
            'märz' => 3, 'march' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'mai' => 5, 'may' => 5,
            'juni' => 6, 'june' => 6, 'jun' => 6,
            'juli' => 7, 'july' => 7, 'jul' => 7,
            'august' => 8, 'aug' => 8,
            'september' => 9, 'sep' => 9, 'sept' => 9,
            'oktober' => 10, 'october' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'dezember' => 12, 'december' => 12, 'dec' => 12,
        ];

        // Try German format: 26. März 2025
        if (preg_match('/(\d{1,2})\.\s*(\w+)\s*(\d{4})/i', $dateStr, $match)) {
            $day = (int) $match[1];
            $monthName = strtolower($match[2]);
            $year = (int) $match[3];

            if (isset($months[$monthName])) {
                return sprintf('%04d-%02d-%02d', $year, $months[$monthName], $day);
            }
        }

        // Try English format: March 26, 2025
        if (preg_match('/(\w+)\s+(\d{1,2}),?\s*(\d{4})/i', $dateStr, $match)) {
            $monthName = strtolower($match[1]);
            $day = (int) $match[2];
            $year = (int) $match[3];

            if (isset($months[$monthName])) {
                return sprintf('%04d-%02d-%02d', $year, $months[$monthName], $day);
            }
        }

        return null;
    }

    /**
     * Format result for display in search results (compatible with other services)
     */
    public function formatForSearch(array $data): array
    {
        return [
            'source' => 'amazon',
            'amazon_url' => $data['url'] ?? null,
            'title' => $data['title'] ?? '',
            'author' => $data['author'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'isbn13' => $data['isbn13'] ?? null,
            'publisher' => $data['publisher'] ?? null,
            'published_date' => $data['published_date'] ?? null,
            'page_count' => $data['page_count'] ?? null,
            'description' => $data['description'] ?? null,
            'language' => $data['language'] ?? null,
            'thumbnail' => $data['thumbnail'] ?? null,
            'series' => $data['series'] ?? null,
            'series_position' => $data['series_position'] ?? null,
        ];
    }
}
