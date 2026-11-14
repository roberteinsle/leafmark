<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Services\DnbService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Fixtures sind echte Antworten der DNB-SRU-Schnittstelle (Metadaten unter CC0),
 * abgefragt mit "Europa im Erdölrausch" bzw. ISBN 9783864894251.
 */
class DnbServiceTest extends TestCase
{
    private function fixture(string $name): string
    {
        return file_get_contents(base_path("tests/Fixtures/{$name}.xml"));
    }

    private function fakeDnb(string $fixture, int $status = 200): void
    {
        Http::fake(['services.dnb.de/*' => Http::response($this->fixture($fixture), $status, ['Content-Type' => 'application/xml'])]);
    }

    public function test_isbn_search_maps_marc_fields(): void
    {
        $this->fakeDnb('dnb_isbn');

        $results = app(DnbService::class)->search('978-3-86489-425-1');

        Http::assertSent(fn (Request $request) => $request['query'] === 'num=9783864894251'
            && $request['version'] === '1.1'
            && $request['recordSchema'] === 'MARC21-xml');

        $this->assertCount(1, $results);
        $book = $results[0];
        $this->assertSame('Europa im Erdölrausch', $book['title']);
        $this->assertSame('die Folgen einer gefährlichen Abhängigkeit', $book['subtitle']);
        $this->assertSame('Daniele Ganser', $book['author']);
        $this->assertSame('9783864894251', $book['isbn13']);
        $this->assertSame('3864894255', $book['isbn']);
        $this->assertSame('Westend', $book['publisher']);
        $this->assertSame('2023-01-01', $book['published_date']);
        $this->assertSame(414, $book['page_count']);
        $this->assertSame('de', $book['language']);
        $this->assertSame('1292640189', $book['dnb_id']);
        $this->assertSame('1292640189', $book['external_id']);
        $this->assertSame('dnb', $book['api_source']);
        $this->assertStringContainsString('covers.openlibrary.org/b/isbn/9783864894251', $book['cover_url']);
    }

    public function test_isbn_prefix_is_recognized(): void
    {
        $this->assertSame('num=9783864894251', app(DnbService::class)->buildCql('isbn:978-3-86489-425-1'));
        $this->assertSame('num=386489425X', app(DnbService::class)->buildCql('3-86489-425-x'));
    }

    public function test_query_types(): void
    {
        $dnb = app(DnbService::class);

        $this->assertSame('woe=Europa im Erdölrausch', $dnb->buildCql('Europa im Erdölrausch'));
        $this->assertSame('tit=Europa im Erdölrausch', $dnb->buildCql('tit:Europa im Erdölrausch'));
        $this->assertSame('per=Ganser, Daniele', $dnb->buildCql('per:Ganser, Daniele'));
        // CQL-Sonderzeichen aus Nutzereingaben werden entfernt
        $this->assertSame('woe=a or b', $dnb->buildCql('a" or (b)='));
        $this->assertNull($dnb->buildCql('  "  '));
    }

    public function test_title_search_returns_books_with_isbn_first(): void
    {
        $this->fakeDnb('dnb_title');

        $results = app(DnbService::class)->search('Europa im Erdölrausch');

        Http::assertSent(fn (Request $request) => $request['query'] === 'woe=Europa im Erdölrausch');
        $this->assertNotEmpty($results);
        $this->assertSame('Europa im Erdölrausch', $results[0]['title']);
        $this->assertNotNull($results[0]['isbn13'] ?? $results[0]['isbn']);

        // Datensätze ohne ISBN (z. B. Online-Ressourcen) stehen hinten
        $seenWithoutIsbn = false;
        foreach ($results as $result) {
            $hasIsbn = (bool) ($result['isbn13'] ?? $result['isbn']);
            $this->assertFalse($seenWithoutIsbn && $hasIsbn, 'Treffer mit ISBN nach Treffer ohne ISBN');
            $seenWithoutIsbn = $seenWithoutIsbn || ! $hasIsbn;
        }
    }

    public function test_narrators_and_translators_are_not_authors(): void
    {
        $this->fakeDnb('dnb_title');

        $authors = collect(app(DnbService::class)->search('Europa im Erdölrausch'))->pluck('author')->implode(' | ');

        $this->assertStringContainsString('Daniele Ganser', $authors);
        $this->assertStringNotContainsString('Böker', $authors);
        $this->assertStringNotContainsString('Smolinsk', $authors);
    }

    public function test_empty_response_returns_empty_array(): void
    {
        $this->fakeDnb('dnb_empty');

        $this->assertSame([], app(DnbService::class)->search('9780000000002'));
    }

    public function test_http_error_returns_empty_array_and_is_not_cached(): void
    {
        Http::fake(['services.dnb.de/*' => Http::sequence()
            ->push('Service Unavailable', 503)
            ->push($this->fixture('dnb_isbn'), 200)]);

        $dnb = app(DnbService::class);

        $this->assertSame([], $dnb->search('9783864894251'));
        $this->assertCount(1, $dnb->search('9783864894251'));
    }

    public function test_timeout_returns_empty_array(): void
    {
        Http::fake(fn () => throw new ConnectionException('Operation timed out after 10000 milliseconds'));

        $this->assertSame([], app(DnbService::class)->search('Europa im Erdölrausch'));
    }

    public function test_invalid_xml_returns_empty_array(): void
    {
        Http::fake(['services.dnb.de/*' => Http::response('<html>Wartung</html', 200)]);

        $this->assertSame([], app(DnbService::class)->search('Europa im Erdölrausch'));
    }

    public function test_results_are_cached_for_24_hours(): void
    {
        $this->fakeDnb('dnb_isbn');
        $dnb = app(DnbService::class);

        $dnb->search('9783864894251');
        $dnb->search('9783864894251');
        Http::assertSentCount(1);

        $this->travel(25)->hours();
        $dnb->search('9783864894251');
        Http::assertSentCount(2);
    }

    public function test_author_name_is_flipped(): void
    {
        $dnb = app(DnbService::class);

        $this->assertSame('Daniele Ganser', $dnb->flipName('Ganser, Daniele'));
        $this->assertSame('Johann Wolfgang von Goethe', $dnb->flipName('Goethe, Johann Wolfgang von'));
        $this->assertSame('Homer', $dnb->flipName('Homer'));
    }

    public function test_page_count_extraction(): void
    {
        $dnb = app(DnbService::class);

        $this->assertSame(414, $dnb->pages('414 Seiten'));
        $this->assertSame(256, $dnb->pages('256 S. : Ill.'));
        $this->assertSame(350, $dnb->pages('XII, 350 S.'));
        $this->assertNull($dnb->pages('Online-Ressource'));
        $this->assertNull($dnb->pages(null));
    }

    public function test_get_book_by_idn(): void
    {
        $this->fakeDnb('dnb_isbn');

        $book = app(DnbService::class)->getBook('1292640189');

        Http::assertSent(fn (Request $request) => $request['query'] === 'idn=1292640189');
        $this->assertSame('Europa im Erdölrausch', $book['title']);
        $this->assertNull(app(DnbService::class)->getBook('not-an-idn'));
    }

    public function test_search_page_offers_dnb_and_adds_book(): void
    {
        $user = $this->actingAsUser();
        $this->fakeDnb('dnb_isbn');
        Http::fake(['covers.openlibrary.org/*' => Http::response('', 404)]);

        $this->get(route('books.create', ['q' => '9783864894251', 'provider' => 'dnb']))
            ->assertOk()
            ->assertSee('value="dnb"', false)
            ->assertSee('Europa im Erdölrausch')
            ->assertSee('name="dnb_id" value="1292640189"', false);

        $this->post(route('books.store-from-api'), ['source' => 'dnb', 'dnb_id' => '1292640189', 'status' => 'want_to_read'])
            ->assertRedirect();

        $book = Book::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('dnb', $book->api_source);
        $this->assertSame('1292640189', $book->external_id);
        $this->assertSame('Daniele Ganser', $book->author);
        $this->assertSame(414, $book->page_count);
        $this->assertSame('2023-01-01', $book->published_date->format('Y-m-d'));
    }

    public function test_german_isbn_puts_dnb_first_in_all_sources(): void
    {
        $this->actingAsUser(['preferred_language' => 'en']);
        $this->fakeDnb('dnb_isbn');
        Http::fake([
            'openlibrary.org/*' => Http::response(['docs' => [[
                'key' => '/works/OL1W', 'title' => 'Open Library Edition', 'isbn' => ['9780000000002'],
            ]]]),
            'bookbrainz.org/*' => Http::response(['results' => []]),
        ]);

        $html = $this->get(route('books.create', ['q' => '978-3-86489-425-1', 'provider' => 'both']))->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'Open Library Edition'), strpos($html, 'Europa im Erdölrausch'));
    }

    public function test_refresh_from_api_supports_dnb(): void
    {
        $user = $this->actingAsUser();
        $this->fakeDnb('dnb_isbn');
        $book = Book::factory()->for($user)->create(['title' => 'Europa im Erdölrausch', 'isbn13' => '9783864894251']);

        $this->getJson(route('books.fetch-api-data', $book).'?source=dnb')
            ->assertOk()
            ->assertJsonPath('fetched.publisher', 'Westend');
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
