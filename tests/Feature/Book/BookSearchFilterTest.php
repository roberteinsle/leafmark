<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use Tests\TestCase;

class BookSearchFilterTest extends TestCase
{
    public function test_can_search_books_by_title(): void
    {
        $user = $this->actingAsUser();
        Book::factory()->create(['user_id' => $user->id, 'title' => 'Laravel Guide']);
        Book::factory()->create(['user_id' => $user->id, 'title' => 'Python Basics']);

        $response = $this->get('/books?search=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel Guide');
        $response->assertDontSee('Python Basics');
    }

    public function test_can_filter_books_by_status(): void
    {
        $user = $this->actingAsUser();
        Book::factory()->create(['user_id' => $user->id, 'title' => 'Reading Now', 'status' => 'currently_reading', 'started_at' => now()]);
        Book::factory()->create(['user_id' => $user->id, 'title' => 'Unread Unique Title', 'status' => 'want_to_read']);

        $response = $this->get('/books?status=currently_reading');

        $response->assertSee('Reading Now');
        $response->assertDontSee('Unread Unique Title');
    }

    public function test_can_filter_books_by_author(): void
    {
        $user = $this->actingAsUser();
        Book::factory()->create(['user_id' => $user->id, 'title' => 'Book A', 'author' => 'John Doe']);
        Book::factory()->create(['user_id' => $user->id, 'title' => 'Book B', 'author' => 'Jane Smith']);

        $response = $this->get('/books?author=' . urlencode('John Doe'));

        $response->assertSee('Book A');
        $response->assertDontSee('Book B');
    }

    public function test_can_sort_books_by_title(): void
    {
        $user = $this->actingAsUser();
        Book::factory()->create(['user_id' => $user->id, 'title' => 'Zebra Book']);
        Book::factory()->create(['user_id' => $user->id, 'title' => 'Apple Book']);

        $response = $this->get('/books?sort=title_asc');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Apple Book', 'Zebra Book']);
    }

    public function test_index_shows_status_counts(): void
    {
        $user = $this->actingAsUser();
        Book::factory()->count(2)->create(['user_id' => $user->id, 'status' => 'want_to_read']);
        Book::factory()->create(['user_id' => $user->id, 'status' => 'currently_reading', 'started_at' => now()]);
        Book::factory()->create(['user_id' => $user->id, 'status' => 'read', 'started_at' => now()->subMonth(), 'finished_at' => now()]);

        $response = $this->get('/books');

        $response->assertViewHas('counts', function ($counts) {
            return $counts['all'] === 4
                && $counts['want_to_read'] === 2
                && $counts['currently_reading'] === 1
                && $counts['read'] === 1;
        });
    }
}
