<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Deutsche Nationalbibliothek (SRU 1.1, MARC21-xml). Kein API-Schlüssel, Metadaten unter CC0.
 *
 * Suche: ISBN → num=, Präfix "tit:" → tit=, "per:" → per=, sonst Freitext woe=.
 * Cover liefert die DNB nicht; bei vorhandener ISBN wird die Open-Library-Cover-URL gesetzt.
 */
class DnbService
{
    private const SRU_URL = 'https://services.dnb.de/sru/dnb';
    private const MARC_NS = 'http://www.loc.gov/MARC21/slim';
    private const CACHE_TTL_HOURS = 24;

    /** MARC-Sprachcodes (ISO 639-2/B) → in Leafmark gespeicherte Codes */
    private const LANGUAGES = [
        'ger' => 'de', 'deu' => 'de', 'eng' => 'en', 'fre' => 'fr', 'fra' => 'fr',
        'ita' => 'it', 'pol' => 'pl', 'spa' => 'es', 'dut' => 'nl', 'nld' => 'nl',
    ];

    public function search(string $query, int $limit = 10, ?string $language = null): array
    {
        $cql = $this->buildCql($query);

        return $cql === null ? [] : $this->fetch($cql, $limit);
    }

    public function getBook(string $idn): ?array
    {
        if (! preg_match('/^[0-9]{8,10}[0-9Xx]?$/', $idn)) {
            return null;
        }

        return $this->fetch('idn='.$idn, 1)[0] ?? null;
    }

    /**
     * ISBN (auch mit Präfix "isbn:") → num=, "tit:"/"per:" → tit=/per=, sonst woe=.
     */
    public function buildCql(string $query): ?string
    {
        $query = trim($query);

        if (preg_match('/^(isbn|tit|per):\s*(.+)$/i', $query, $m)) {
            $prefix = strtolower($m[1]);
            $term = $m[2];
        } else {
            $prefix = null;
            $term = $query;
        }

        $compact = str_replace(['-', ' '], '', $term);
        if ($prefix === 'isbn' || ($prefix === null && preg_match('/^(\d{9}[\dXx]|\d{13})$/', $compact))) {
            return preg_match('/^(\d{9}[\dXx]|\d{13})$/', $compact) ? 'num='.strtoupper($compact) : null;
        }

        // CQL-Sonderzeichen entfernen, damit Nutzereingaben die Abfrage nicht umbauen
        $term = trim(preg_replace('/\s+/', ' ', preg_replace('/["()=<>\/\\\\*?]/u', ' ', $term)));
        if ($term === '') {
            return null;
        }

        return match ($prefix) {
            'tit' => 'tit='.$term,
            'per' => 'per='.$term,
            default => 'woe='.$term,
        };
    }

    private function fetch(string $cql, int $limit): array
    {
        $cacheKey = 'dnb:'.md5($cql.'|'.$limit);
        if (($cached = Cache::get($cacheKey)) !== null) {
            return $cached;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Leafmark/'.config('app.version').' (+'.config('app.url').')'])
                ->get(self::SRU_URL, [
                    'version' => '1.1',
                    'operation' => 'searchRetrieve',
                    'recordSchema' => 'MARC21-xml',
                    'maximumRecords' => $limit,
                    'query' => $cql,
                ]);

            if (! $response->successful()) {
                Log::warning('DNB SRU request failed', ['status' => $response->status(), 'query' => $cql]);

                return [];
            }

            $results = $this->parse($response->body());
        } catch (\Throwable $e) {
            Log::error('DNB SRU error', ['error' => $e->getMessage(), 'query' => $cql]);

            return [];
        }

        // Nur erfolgreiche Antworten cachen (auch leere: "nichts gefunden" ändert sich selten)
        Cache::put($cacheKey, $results, now()->addHours(self::CACHE_TTL_HOURS));

        return $results;
    }

    /**
     * Wandelt eine SRU-Antwort mit MARC21-Datensätzen in das Leafmark-Ergebnisformat um.
     * Datensätze mit ISBN (gedruckte Bücher) kommen vor solchen ohne (z. B. Online-Ressourcen).
     */
    public function parse(string $xml): array
    {
        $dom = new DOMDocument;
        if (trim($xml) === '' || ! @$dom->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('m', self::MARC_NS);

        $results = [];
        foreach ($xpath->query('//m:record') as $record) {
            $book = $this->formatRecord($xpath, $record);
            if ($book !== null) {
                $results[] = $book;
            }
        }

        $withIsbn = array_filter($results, fn ($r) => $r['isbn13'] || $r['isbn']);
        $withoutIsbn = array_filter($results, fn ($r) => ! $r['isbn13'] && ! $r['isbn']);

        return array_values([...$withIsbn, ...$withoutIsbn]);
    }

    private function formatRecord(DOMXPath $xpath, DOMElement $record): ?array
    {
        $title = $this->clean($this->first($xpath, $record, '245', 'a'));
        if (! $title) {
            return null;
        }

        $idn = trim((string) $xpath->query('m:controlfield[@tag="001"]', $record)->item(0)?->nodeValue) ?: null;
        [$isbn13, $isbn] = $this->isbns($xpath, $record);
        $coverIsbn = $isbn13 ?? $isbn;

        $publisher = $this->first($xpath, $record, '264', 'b') ?? $this->first($xpath, $record, '260', 'b');
        $year = $this->year($this->first($xpath, $record, '264', 'c') ?? $this->first($xpath, $record, '260', 'c'));
        $languageCode = strtolower((string) $this->first($xpath, $record, '041', 'a'));

        return [
            'title' => $title,
            'subtitle' => $this->clean($this->first($xpath, $record, '245', 'b')),
            'author' => $this->authors($xpath, $record) ?: null,
            'isbn' => $isbn,
            'isbn13' => $isbn13,
            'publisher' => $this->clean($publisher),
            'published_date' => $year ? $year.'-01-01' : null,
            'description' => null,
            'page_count' => $this->pages($this->first($xpath, $record, '300', 'a')),
            'language' => self::LANGUAGES[$languageCode] ?? ($languageCode ?: null),
            'series' => $this->clean($this->first($xpath, $record, '490', 'a')),
            'series_position' => $this->seriesPosition($this->first($xpath, $record, '490', 'v')),
            'cover_url' => $coverIsbn ? "https://covers.openlibrary.org/b/isbn/{$coverIsbn}-L.jpg?default=false" : null,
            'thumbnail' => $coverIsbn ? "https://covers.openlibrary.org/b/isbn/{$coverIsbn}-M.jpg" : null,
            'dnb_id' => $idn,
            'external_id' => $idn,
            'api_source' => 'dnb',
        ];
    }

    /** Erstes Unterfeld $code des ersten Datenfelds $tag, das es enthält */
    private function first(DOMXPath $xpath, DOMElement $record, string $tag, string $code): ?string
    {
        $node = $xpath->query("m:datafield[@tag=\"{$tag}\"]/m:subfield[@code=\"{$code}\"]", $record)->item(0);

        return $node ? $this->text($node->nodeValue) : null;
    }

    /**
     * Die DNB liefert Umlaute zerlegt (NFD: "o" + kombinierendes Trema) und markiert nicht
     * sortierende Artikel mit U+0098/U+009C. Beides würde Suche und Dublettenprüfung stören.
     */
    private function text(string $value): string
    {
        $value = preg_replace('/[\x{0080}-\x{009F}]/u', '', $value) ?? $value;
        if (class_exists(\Normalizer::class)) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_C) ?: $value;
        }

        return trim($value);
    }

    /**
     * 100 $a (Hauptverfasser) und 700 $a, aus 700 aber nur Personen mit Rolle "aut"
     * (dort stehen sonst auch Sprecher, Übersetzer, Herausgeber). "Nachname, Vorname" → "Vorname Nachname".
     */
    private function authors(DOMXPath $xpath, DOMElement $record): string
    {
        $names = [];
        foreach ($xpath->query('m:datafield[@tag="100" or @tag="700"]', $record) as $field) {
            $name = $xpath->query('m:subfield[@code="a"]', $field)->item(0)?->nodeValue;
            if (! $name) {
                continue;
            }

            if ($field->getAttribute('tag') === '700') {
                $roles = [];
                foreach ($xpath->query('m:subfield[@code="4" or @code="e"]', $field) as $role) {
                    $roles[] = strtolower(trim($role->nodeValue));
                }
                if (! array_intersect($roles, ['aut', 'verfasser', 'verfasserin', 'author'])) {
                    continue;
                }
            }

            $names[] = $this->flipName($this->text($name));
        }

        return implode(', ', array_unique($names));
    }

    public function flipName(string $name): string
    {
        $name = trim($name, " \t\n,.");
        if (substr_count($name, ',') === 1) {
            [$last, $first] = array_map('trim', explode(',', $name));

            return trim("{$first} {$last}");
        }

        return $name;
    }

    /**
     * 020 $a enthält die ISBN, oft mit Zusätzen ("kart. : EUR 24.00"). ISBN-13 wird bevorzugt.
     *
     * @return array{0: ?string, 1: ?string} [isbn13, isbn10]
     */
    private function isbns(DOMXPath $xpath, DOMElement $record): array
    {
        $isbn13 = null;
        $isbn10 = null;

        foreach ($xpath->query('m:datafield[@tag="020"]/m:subfield[@code="a" or @code="9"]', $record) as $node) {
            if (! preg_match('/(97[89][\d-]{10,16}|\d[\d-]{8,12}[\dXx])/', $node->nodeValue, $m)) {
                continue;
            }
            $digits = strtoupper(str_replace('-', '', $m[1]));
            if (strlen($digits) === 13 && ! $isbn13) {
                $isbn13 = $digits;
            } elseif (strlen($digits) === 10 && ! $isbn10) {
                $isbn10 = $digits;
            }
        }

        return [$isbn13, $isbn10];
    }

    /** "414 Seiten", "414 S.", "XII, 414 S. : Ill." → 414 */
    public function pages(?string $extent): ?int
    {
        if ($extent && preg_match('/(\d+)\s*(S\.|Seiten|Seite|p\.|pages)/iu', $extent, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /** "[2023]", "c 2022", "2021-" → nur die 4-stellige Jahreszahl */
    private function year(?string $value): ?string
    {
        return $value && preg_match('/\b(1[5-9]\d{2}|20\d{2})\b/', $value, $m) ? $m[1] : null;
    }

    /** "Band 3", "3" → 3 (books.series_position ist eine Ganzzahl-Spalte) */
    private function seriesPosition(?string $value): ?int
    {
        return $value && preg_match('/(\d+)/', $value, $m) ? (int) $m[1] : null;
    }

    /** MARC-Satzzeichen am Ende entfernen ("Titel :" / "Verlag," / "Reihe ;") */
    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim(preg_replace('/[\s\/:;,=.]+$/u', '', $value));

        return $value === '' ? null : $value;
    }
}
