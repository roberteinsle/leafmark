<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AmazonProductService
{
    protected ?string $accessKey;
    protected ?string $secretKey;
    protected ?string $associateTag;
    protected string $region = 'com'; // Default to Amazon.com
    protected string $host;
    protected string $path = '/paapi5/searchitems';

    public function __construct()
    {
        // Try user's API credentials first, fallback to env config
        $user = auth()->user();

        $this->accessKey = $user && $user->amazon_access_key
            ? $user->amazon_access_key
            : config('services.amazon.access_key');

        $this->secretKey = $user && $user->amazon_secret_key
            ? $user->amazon_secret_key
            : config('services.amazon.secret_key');

        $this->associateTag = $user && $user->amazon_associate_tag
            ? $user->amazon_associate_tag
            : config('services.amazon.associate_tag');

        $this->host = 'webservices.amazon.' . $this->region;
    }

    /**
     * Search for books on Amazon
     */
    public function search(string $query, int $maxResults = 20, string $language = null): array
    {
        // Check if credentials are configured
        if (!$this->accessKey || !$this->secretKey || !$this->associateTag) {
            Log::warning('Amazon API credentials not configured');
            return [];
        }

        // Build search query
        $searchQuery = $this->buildSearchQuery($query);

        try {
            // Prepare request payload
            $payload = [
                'Keywords' => $searchQuery,
                'Resources' => [
                    'Images.Primary.Large',
                    'Images.Primary.Medium',
                    'ItemInfo.Title',
                    'ItemInfo.ByLineInfo',
                    'ItemInfo.ContentInfo',
                    'ItemInfo.Classifications',
                ],
                'SearchIndex' => 'Books',
                'ItemCount' => min($maxResults, 10), // Amazon allows max 10 per request
                'PartnerTag' => $this->associateTag,
                'PartnerType' => 'Associates',
                'Marketplace' => 'www.amazon.' . $this->region,
            ];

            // Add language filter if specified
            if ($language) {
                $payload['Languages'] = [$language];
            }

            // Sign and send request
            $response = $this->sendSignedRequest($payload);

            if (isset($response['SearchResult']['Items'])) {
                return $this->formatResults($response['SearchResult']['Items']);
            }

            return [];

        } catch (\Exception $e) {
            Log::error('Amazon Product API exception', [
                'message' => $e->getMessage(),
                'query' => $searchQuery,
            ]);

            return [];
        }
    }

    /**
     * Get a single book by ASIN
     */
    public function getBook(string $asin): ?array
    {
        if (!$this->accessKey || !$this->secretKey || !$this->associateTag) {
            return null;
        }

        try {
            $payload = [
                'ItemIds' => [$asin],
                'Resources' => [
                    'Images.Primary.Large',
                    'Images.Primary.Medium',
                    'ItemInfo.Title',
                    'ItemInfo.ByLineInfo',
                    'ItemInfo.ContentInfo',
                    'ItemInfo.Classifications',
                ],
                'PartnerTag' => $this->associateTag,
                'PartnerType' => 'Associates',
                'Marketplace' => 'www.amazon.' . $this->region,
            ];

            $response = $this->sendSignedRequest($payload, '/paapi5/getitems');

            if (isset($response['ItemsResult']['Items'][0])) {
                return $this->formatBook($response['ItemsResult']['Items'][0]);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Amazon Product API exception', [
                'message' => $e->getMessage(),
                'asin' => $asin,
            ]);

            return null;
        }
    }

    /**
     * Build search query from input
     */
    protected function buildSearchQuery(string $query): string
    {
        $query = trim($query);

        // Clean query for ISBN check (remove hyphens and spaces)
        $cleanQuery = str_replace(['-', ' '], '', $query);

        // ISBN-10 (10 digits) or ISBN-13 (13 digits)
        if (preg_match('/^\d{10}$|^\d{13}$/', $cleanQuery)) {
            return $cleanQuery; // Amazon searches ISBN directly
        }

        return $query;
    }

    /**
     * Send signed request to Amazon PA-API
     */
    protected function sendSignedRequest(array $payload, string $path = null): array
    {
        $path = $path ?? $this->path;
        $timestamp = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');

        // Create canonical request
        $payloadJson = json_encode($payload);

        $headers = [
            'content-encoding' => 'amz-1.0',
            'content-type' => 'application/json; charset=utf-8',
            'host' => $this->host,
            'x-amz-date' => $timestamp,
            'x-amz-target' => 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems',
        ];

        if (strpos($path, 'getitems') !== false) {
            $headers['x-amz-target'] = 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems';
        }

        // Create signature
        $signature = $this->createSignature($headers, $payloadJson, $timestamp, $date, $path);

        // Send request
        $response = Http::withHeaders([
            'Authorization' => $signature,
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Encoding' => 'amz-1.0',
            'Host' => $this->host,
            'X-Amz-Date' => $timestamp,
            'X-Amz-Target' => $headers['x-amz-target'],
        ])->post('https://' . $this->host . $path, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Amazon API error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [];
    }

    /**
     * Create AWS Signature Version 4
     */
    protected function createSignature(array $headers, string $payload, string $timestamp, string $date, string $path): string
    {
        // Step 1: Create canonical request
        $canonicalHeaders = '';
        $signedHeaders = '';
        ksort($headers);

        foreach ($headers as $key => $value) {
            $canonicalHeaders .= strtolower($key) . ':' . trim($value) . "\n";
            $signedHeaders .= strtolower($key) . ';';
        }
        $signedHeaders = rtrim($signedHeaders, ';');

        $payloadHash = hash('sha256', $payload);

        $canonicalRequest = "POST\n" .
            $path . "\n" .
            "\n" .
            $canonicalHeaders . "\n" .
            $signedHeaders . "\n" .
            $payloadHash;

        // Step 2: Create string to sign
        $credentialScope = $date . '/us-east-1/ProductAdvertisingAPI/aws4_request';
        $stringToSign = "AWS4-HMAC-SHA256\n" .
            $timestamp . "\n" .
            $credentialScope . "\n" .
            hash('sha256', $canonicalRequest);

        // Step 3: Calculate signature
        $kDate = hash_hmac('sha256', $date, 'AWS4' . $this->secretKey, true);
        $kRegion = hash_hmac('sha256', 'us-east-1', $kDate, true);
        $kService = hash_hmac('sha256', 'ProductAdvertisingAPI', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        // Step 4: Create authorization header
        return 'AWS4-HMAC-SHA256 Credential=' . $this->accessKey . '/' . $credentialScope .
            ', SignedHeaders=' . $signedHeaders .
            ', Signature=' . $signature;
    }

    /**
     * Format API response to consistent structure
     */
    protected function formatResults(array $items): array
    {
        return array_map(function ($item) {
            return $this->formatBook($item);
        }, $items);
    }

    /**
     * Format a single book item
     */
    protected function formatBook(array $item): array
    {
        $itemInfo = $item['ItemInfo'] ?? [];
        $images = $item['Images']['Primary'] ?? [];

        // Extract ISBN
        $isbn = null;
        $isbn13 = null;
        if (isset($itemInfo['ContentInfo']['PublicationDate'])) {
            foreach ($itemInfo['ExternalIds']['ISBNs']['DisplayValues'] ?? [] as $isbnValue) {
                $clean = str_replace('-', '', $isbnValue);
                if (strlen($clean) === 10) {
                    $isbn = $clean;
                } elseif (strlen($clean) === 13) {
                    $isbn13 = $clean;
                }
            }
        }

        // Extract authors
        $authors = [];
        if (isset($itemInfo['ByLineInfo']['Contributors']['DisplayValues'])) {
            foreach ($itemInfo['ByLineInfo']['Contributors']['DisplayValues'] as $contributor) {
                if (isset($contributor['Role']) && $contributor['Role'] === 'Author') {
                    $authors[] = $contributor['Name'];
                }
            }
        }

        return [
            'asin' => $item['ASIN'] ?? null,
            'title' => $itemInfo['Title']['DisplayValue'] ?? 'Unknown Title',
            'subtitle' => null,
            'author' => !empty($authors) ? implode(', ', $authors) : null,
            'publisher' => $itemInfo['ByLineInfo']['Manufacturer']['DisplayValue'] ?? null,
            'published_date' => $itemInfo['ContentInfo']['PublicationDate']['DisplayValue'] ?? null,
            'description' => null, // Not available in basic search
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'page_count' => $itemInfo['ContentInfo']['PagesCount']['DisplayValue'] ?? null,
            'categories' => [],
            'language' => $itemInfo['ContentInfo']['Languages']['DisplayValues'][0]['DisplayValue'] ?? 'en',
            'thumbnail' => $images['Medium']['URL'] ?? null,
            'cover_url' => $images['Large']['URL'] ?? null,
            'preview_link' => $item['DetailPageURL'] ?? null,
            'info_link' => $item['DetailPageURL'] ?? null,
        ];
    }
}
