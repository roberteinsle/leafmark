<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBooksService
{
    /** Fehlercodes, die der Oberfläche gemeldet werden */
    public const ERROR_QUOTA = 'quota';
    public const ERROR_KEY_REJECTED = 'key_rejected';
    public const ERROR_UNAVAILABLE = 'unavailable';

    protected ?string $apiKey;
    protected string $baseUrl = 'https://www.googleapis.com/books/v1';

    /** Letzter Fehler der zuletzt ausgeführten Suche (null = ok) */
    public ?string $lastError = null;

    public function __construct()
    {
        // Schlüssel aus Admin → Systemeinstellungen, Fallback auf GOOGLE_BOOKS_API_KEY
        $this->apiKey = SystemSetting::getSecret('google_books_api_key')
            ?: config('services.google_books.api_key');
    }

    public function hasApiKey(): bool
    {
        return (bool) $this->apiKey;
    }

    /**
     * Search for books. ISBNs are searched exactly, everything else uses Google's relevance search.
     */
    public function search(string $query, int $maxResults = 20, ?string $language = null): array
    {
        $this->lastError = null;
        $searchQuery = $this->buildSearchQuery($query);

        if ($language) {
            $results = $this->fetchVolumes($searchQuery, $maxResults, $language);

            if (count($results) >= 5 || $this->lastError) {
                return $results;
            }
            // Wenige Treffer in der Sprache: ohne Einschränkung suchen und bevorzugt sortieren
        }

        $results = $this->fetchVolumes($searchQuery, $maxResults);

        return $language ? $this->sortByLanguagePreference($results, $language) : $results;
    }

    /**
     * Get a single book by ID
     */
    public function getBook(string $googleBooksId): ?array
    {
        $this->lastError = null;
        $response = $this->request("volumes/{$googleBooksId}", []);

        return $response?->successful() ? $this->formatBook($response->json()) : null;
    }

    /**
     * Prüfabfrage für Admin → Systemeinstellungen.
     *
     * @return array{ok: bool, status: int|null, message: string}
     */
    public function testConnection(?string $apiKey = null): array
    {
        $params = ['q' => 'isbn:9780141439518', 'maxResults' => 1];
        if ($apiKey ?? $this->apiKey) {
            $params['key'] = $apiKey ?? $this->apiKey;
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/volumes", $params);
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => null, 'message' => $e->getMessage()];
        }

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'message' => $response->successful()
                ? 'OK'
                : ($response->json('error.message') ?? $response->reason()),
        ];
    }

    protected function fetchVolumes(string $query, int $maxResults, ?string $language = null): array
    {
        $params = array_filter([
            'q' => $query,
            'maxResults' => $maxResults,
            'orderBy' => 'relevance',
            'printType' => 'books',
            'langRestrict' => $language,
        ]);

        $response = $this->request('volumes', $params);

        return $response?->successful() ? $this->formatResults($response->json()) : [];
    }

    /**
     * GET mit Timeout. Lehnt Google den Schlüssel ab (z. B. IP-Einschränkung passt nicht),
     * wird einmal ohne Schlüssel versucht und der Grund in $lastError festgehalten.
     */
    protected function request(string $path, array $params): ?Response
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$path}", $this->apiKey ? $params + ['key' => $this->apiKey] : $params);

            if ($this->apiKey && in_array($response->status(), [400, 403], true)) {
                Log::warning('Google Books API rejected the API key, retrying without key', [
                    'status' => $response->status(),
                    'reason' => $response->json('error.message'),
                ]);
                $this->lastError = self::ERROR_KEY_REJECTED;
                $response = Http::timeout(10)->get("{$this->baseUrl}/{$path}", $params);
            }

            if ($response->status() === 429) {
                $this->lastError = self::ERROR_QUOTA;
            } elseif (! $response->successful() && $this->lastError === null) {
                $this->lastError = self::ERROR_UNAVAILABLE;
            }

            if (! $response->successful()) {
                Log::error('Google Books API error', ['status' => $response->status(), 'reason' => $response->json('error.message')]);
            }

            return $response;
        } catch (\Throwable $e) {
            Log::error('Google Books API exception', ['message' => $e->getMessage()]);
            $this->lastError = self::ERROR_UNAVAILABLE;

            return null;
        }
    }

    /**
     * ISBN → isbn:-Suche; manuelle Präfixe (intitle:, inauthor:, …) bleiben erhalten;
     * alles andere geht unverändert an Googles Relevanzsuche. Früher wurde aus
     * „zwei großgeschriebenen Wörtern“ eine Autorensuche – das traf fast jeden deutschen Titel.
     */
    protected function buildSearchQuery(string $query): string
    {
        $query = trim($query);

        if (preg_match('/^(isbn|intitle|inauthor|inpublisher|subject):/i', $query)) {
            return $query;
        }

        $clean = str_replace(['-', ' '], '', $query);
        if (preg_match('/^(\d{9}[\dXx]|\d{13})$/', $clean)) {
            return 'isbn:'.strtoupper($clean);
        }

        return $query;
    }

    /**
     * Preferred language first, otherwise keep Google's relevance order.
     */
    protected function sortByLanguagePreference(array $results, string $preferredLanguage): array
    {
        $preferred = array_filter($results, fn ($r) => ($r['language'] ?? '') === $preferredLanguage);
        $others = array_filter($results, fn ($r) => ($r['language'] ?? '') !== $preferredLanguage);

        return array_values([...$preferred, ...$others]);
    }

    protected function formatResults(array $data): array
    {
        return array_map(fn ($item) => $this->formatBook($item), $data['items'] ?? []);
    }

    protected function formatBook(array $item): array
    {
        $volumeInfo = $item['volumeInfo'] ?? [];

        $isbn = null;
        $isbn13 = null;
        foreach ($volumeInfo['industryIdentifiers'] ?? [] as $identifier) {
            if ($identifier['type'] === 'ISBN_10') {
                $isbn = $identifier['identifier'];
            } elseif ($identifier['type'] === 'ISBN_13') {
                $isbn13 = $identifier['identifier'];
            }
        }

        $authors = $volumeInfo['authors'] ?? [];
        $images = $volumeInfo['imageLinks'] ?? [];

        return [
            'google_books_id' => $item['id'] ?? null,
            'title' => $volumeInfo['title'] ?? 'Unknown Title',
            'subtitle' => $volumeInfo['subtitle'] ?? null,
            'author' => $authors ? implode(', ', $authors) : null,
            'publisher' => $volumeInfo['publisher'] ?? null,
            'published_date' => $volumeInfo['publishedDate'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'page_count' => $volumeInfo['pageCount'] ?? null,
            'categories' => $volumeInfo['categories'] ?? [],
            'language' => $volumeInfo['language'] ?? 'en',
            'thumbnail' => $this->https($images['thumbnail'] ?? null),
            'cover_url' => $this->https($images['medium'] ?? $images['large'] ?? $images['thumbnail'] ?? null),
            'preview_link' => $volumeInfo['previewLink'] ?? null,
            'info_link' => $volumeInfo['infoLink'] ?? null,
        ];
    }

    /** Google liefert Bild-URLs als http:// – auf einer https-Seite wären das Mixed-Content-Warnungen */
    protected function https(?string $url): ?string
    {
        return $url ? preg_replace('#^http://#', 'https://', $url) : null;
    }
}
