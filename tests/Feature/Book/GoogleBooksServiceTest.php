<?php

namespace Tests\Feature\Book;

use App\Models\SystemSetting;
use App\Services\GoogleBooksService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBooksServiceTest extends TestCase
{
    private function volume(string $title, string $lang = 'de'): array
    {
        return ['id' => md5($title), 'volumeInfo' => [
            'title' => $title,
            'language' => $lang,
            'imageLinks' => ['thumbnail' => 'http://books.google.com/books/content?id=x'],
        ]];
    }

    public function test_capitalized_titles_are_not_turned_into_author_searches(): void
    {
        Http::fake(['www.googleapis.com/*' => Http::response(['items' => [$this->volume('Der Schwarm')]])]);

        (new GoogleBooksService)->search('Der Schwarm', 10);

        Http::assertSent(fn ($request) => $request['q'] === 'Der Schwarm');
    }

    public function test_isbn_is_searched_exactly(): void
    {
        Http::fake(['www.googleapis.com/*' => Http::response(['items' => []])]);

        (new GoogleBooksService)->search('978-3-596-16453-0', 10);

        Http::assertSent(fn ($request) => $request['q'] === 'isbn:9783596164530');
    }

    public function test_cover_urls_use_https(): void
    {
        Http::fake(['www.googleapis.com/*' => Http::response(['items' => [$this->volume('Buch')]])]);

        $results = (new GoogleBooksService)->search('Buch', 10);

        $this->assertStringStartsWith('https://', $results[0]['thumbnail']);
    }

    public function test_rejected_key_retries_without_key_and_reports_it(): void
    {
        SystemSetting::setSecret('google_books_api_key', 'AIza-test-key');
        Http::fake([
            'www.googleapis.com/*' => Http::sequence()
                ->push(['error' => ['message' => 'IP blocked']], 403)
                ->push(['items' => [$this->volume('Buch')]], 200),
        ]);

        $service = new GoogleBooksService;
        $results = $service->search('Buch', 10);

        $this->assertCount(1, $results);
        $this->assertSame(GoogleBooksService::ERROR_KEY_REJECTED, $service->lastError);
        Http::assertSentCount(2);
    }

    public function test_quota_error_is_reported(): void
    {
        Http::fake(['www.googleapis.com/*' => Http::response(['error' => ['message' => 'Quota exceeded']], 429)]);

        $service = new GoogleBooksService;

        $this->assertSame([], $service->search('Buch', 10));
        $this->assertSame(GoogleBooksService::ERROR_QUOTA, $service->lastError);
    }

    public function test_search_page_shows_quota_notice(): void
    {
        $this->actingAsUser();
        SystemSetting::setSecret('google_books_api_key', 'AIza-test-key');
        Http::fake(['www.googleapis.com/*' => Http::response(['error' => ['message' => 'Quota exceeded']], 429)]);

        $this->get(route('books.create', ['q' => 'Buch', 'provider' => 'google']))
            ->assertOk()
            ->assertSee(__('app.books.provider_error_quota', ['source' => __('app.books.google_books')]));
    }

    public function test_google_option_appears_when_key_is_in_system_settings(): void
    {
        $this->actingAsUser();
        $this->get(route('books.create'))->assertDontSee('value="google"', false);

        SystemSetting::setSecret('google_books_api_key', 'AIza-test-key');
        $this->get(route('books.create'))->assertSee('value="google"', false);
    }
}
