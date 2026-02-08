<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use Tests\TestCase;

class BookStatusTest extends TestCase
{
    public function test_user_can_update_status_to_currently_reading(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->create(['user_id' => $user->id, 'status' => 'want_to_read']);

        $response = $this->patch("/books/{$book->added_at->timestamp}/status", [
            'status' => 'currently_reading',
        ]);

        $response->assertRedirect();
        $book->refresh();
        $this->assertEquals('currently_reading', $book->status);
        $this->assertNotNull($book->started_at);
    }

    public function test_user_can_update_status_to_read(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'status' => 'currently_reading',
            'page_count' => 300,
        ]);

        $response = $this->patch("/books/{$book->added_at->timestamp}/status", [
            'status' => 'read',
        ]);

        $response->assertRedirect();
        $book->refresh();
        $this->assertEquals('read', $book->status);
        $this->assertNotNull($book->finished_at);
        $this->assertEquals(300, $book->current_page);
    }

    public function test_user_can_update_status_back_to_want_to_read(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->currentlyReading()->create(['user_id' => $user->id]);

        $response = $this->patch("/books/{$book->added_at->timestamp}/status", [
            'status' => 'want_to_read',
        ]);

        $response->assertRedirect();
        $book->refresh();
        $this->assertEquals('want_to_read', $book->status);
        $this->assertNull($book->started_at);
    }

    public function test_user_can_update_reading_progress(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->currentlyReading()->create([
            'user_id' => $user->id,
            'page_count' => 300,
        ]);

        $response = $this->patch("/books/{$book->added_at->timestamp}/progress", [
            'current_page' => 150,
        ]);

        $response->assertRedirect();
        $book->refresh();
        $this->assertEquals(150, $book->current_page);
        $this->assertDatabaseHas('reading_progress_history', [
            'book_id' => $book->id,
            'page_number' => 150,
        ]);
    }

    public function test_user_can_update_rating(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->read()->create(['user_id' => $user->id]);

        $response = $this->patch("/books/{$book->added_at->timestamp}/rating", [
            'rating' => 4.5,
            'review' => 'Great book!',
        ]);

        $response->assertRedirect();
        $book->refresh();
        $this->assertEquals(4.5, (float) $book->rating);
        $this->assertEquals('Great book!', $book->review);
    }

    public function test_rating_must_be_between_0_and_5(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->read()->create(['user_id' => $user->id]);

        $this->patch("/books/{$book->added_at->timestamp}/rating", [
            'rating' => 6,
        ])->assertSessionHasErrors(['rating']);

        $this->patch("/books/{$book->added_at->timestamp}/rating", [
            'rating' => -1,
        ])->assertSessionHasErrors(['rating']);
    }

    public function test_user_cannot_update_status_of_another_users_book(): void
    {
        $this->actingAsUser();
        $otherUser = $this->createUser();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);

        $this->patch("/books/{$book->added_at->timestamp}/status", [
            'status' => 'read',
        ])->assertStatus(404);
    }
}
