<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchApiDataTest extends TestCase
{
    private function volumes(array $items): array
    {
        return ['items' => array_map(fn ($title) => ['id' => md5($title), 'volumeInfo' => ['title' => $title, 'authors' => ['Some Author']]], $items)];
    }

    public function test_foreign_external_id_is_not_sent_to_google(): void
    {
        $user = $this->actingAsUser();
        SystemSetting::setSecret('google_books_api_key', 'AIza-test-key');
        // Buch aus Open Library, ohne ISBN – wie im gemeldeten Fehler
        $book = Book::factory()->for($user)->create([
            'title' => 'Kampf der Giganten', 'author' => null, 'isbn' => null, 'isbn13' => null,
            'api_source' => 'openlibrary', 'external_id' => 'OL12345M',
        ]);
        Http::fake(['www.googleapis.com/*' => Http::response($this->volumes(['Kampf der Giganten']))]);

        $this->getJson(route('books.fetch-api-data', $book).'?source=google')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('fetched.title', 'Kampf der Giganten');

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'OL12345M'));
        Http::assertSent(fn ($request) => ($request['q'] ?? null) === 'Kampf der Giganten');
    }

    public function test_own_external_id_is_used_for_its_source(): void
    {
        $user = $this->actingAsUser();
        SystemSetting::setSecret('google_books_api_key', 'AIza-test-key');
        $book = Book::factory()->for($user)->create(['api_source' => 'google', 'external_id' => 'gb-123']);
        Http::fake(['www.googleapis.com/books/v1/volumes/gb-123*' => Http::response(['id' => 'gb-123', 'volumeInfo' => ['title' => 'From ID']])]);

        $this->getJson(route('books.fetch-api-data', $book).'?source=google')->assertOk()->assertJsonPath('fetched.title', 'From ID');
    }

    public function test_isbn_is_preferred_over_title(): void
    {
        $user = $this->actingAsUser();
        SystemSetting::setSecret('google_books_api_key', 'AIza-test-key');
        $book = Book::factory()->for($user)->create(['isbn13' => '9783596164530', 'api_source' => null, 'external_id' => null]);
        Http::fake(['www.googleapis.com/*' => Http::response($this->volumes(['Der Schwarm']))]);

        $this->getJson(route('books.fetch-api-data', $book).'?source=google')->assertOk();

        Http::assertSent(fn ($request) => ($request['q'] ?? null) === 'isbn:9783596164530');
    }

    public function test_no_match_returns_translated_message(): void
    {
        $user = $this->actingAsUser();
        SystemSetting::setSecret('google_books_api_key', 'AIza-test-key');
        $book = Book::factory()->for($user)->create(['isbn' => null, 'isbn13' => null, 'external_id' => null]);
        Http::fake(['www.googleapis.com/*' => Http::response(['totalItems' => 0])]);

        $this->getJson(route('books.fetch-api-data', $book).'?source=google')
            ->assertNotFound()
            ->assertJsonPath('error', __('app.books.api_no_data', ['source' => 'Google Books']));
    }

    public function test_unknown_source_is_rejected(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->for($user)->create();

        $this->getJson(route('books.fetch-api-data', $book).'?source=nope')->assertStatus(422);
    }
}
