<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use Tests\TestCase;

class BookTest extends TestCase
{
    public function test_reading_progress_calculation(): void
    {
        $user = $this->createUser();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'page_count' => 200,
            'current_page' => 100,
        ]);

        $this->assertEquals(50, $book->reading_progress);
    }

    public function test_reading_progress_zero_when_no_page_count(): void
    {
        $user = $this->createUser();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'page_count' => 0,
            'current_page' => 50,
        ]);

        $this->assertEquals(0, $book->reading_progress);

        $book2 = Book::factory()->create([
            'user_id' => $user->id,
            'page_count' => null,
            'current_page' => 0,
        ]);

        $this->assertEquals(0, $book2->reading_progress);
    }

    public function test_mark_as_started_sets_status_and_timestamp(): void
    {
        $user = $this->createUser();
        $book = Book::factory()->create(['user_id' => $user->id, 'status' => 'want_to_read']);

        $book->markAsStarted();
        $book->refresh();

        $this->assertEquals('currently_reading', $book->status);
        $this->assertNotNull($book->started_at);
    }

    public function test_mark_as_finished_sets_status_and_timestamps(): void
    {
        $user = $this->createUser();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'status' => 'currently_reading',
            'page_count' => 300,
        ]);

        $book->markAsFinished();
        $book->refresh();

        $this->assertEquals('read', $book->status);
        $this->assertNotNull($book->finished_at);
        $this->assertEquals(300, $book->current_page);
    }

    public function test_route_key_returns_timestamp(): void
    {
        $user = $this->createUser();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($book->added_at->timestamp, $book->getRouteKey());
    }

    public function test_scopes_filter_correctly(): void
    {
        $user = $this->createUser();

        Book::factory()->count(2)->create(['user_id' => $user->id, 'status' => 'want_to_read']);
        Book::factory()->count(3)->create(['user_id' => $user->id, 'status' => 'currently_reading', 'started_at' => now()]);
        Book::factory()->create(['user_id' => $user->id, 'status' => 'read', 'started_at' => now()->subMonth(), 'finished_at' => now()]);

        $this->assertEquals(2, $user->books()->wantToRead()->count());
        $this->assertEquals(3, $user->books()->currentlyReading()->count());
        $this->assertEquals(1, $user->books()->read()->count());
    }
}
