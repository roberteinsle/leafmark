<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\User;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    public function test_user_can_view_book_index(): void
    {
        $user = $this->actingAsUser();
        Book::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertViewHas('books');
    }

    public function test_book_index_only_shows_own_books(): void
    {
        $user = $this->actingAsUser();
        $otherUser = $this->createUser();

        Book::factory()->create(['user_id' => $user->id, 'title' => 'My Book']);
        Book::factory()->create(['user_id' => $otherUser->id, 'title' => 'Other Book']);

        $response = $this->get('/books');

        $response->assertSee('My Book');
        $response->assertDontSee('Other Book');
    }

    public function test_user_can_view_create_form(): void
    {
        $this->actingAsUser();

        $this->get('/books/create')->assertStatus(200);
    }

    public function test_user_can_store_a_book(): void
    {
        $user = $this->actingAsUser();

        $response = $this->post('/books', [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'status' => 'want_to_read',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'Test Book',
            'author' => 'Test Author',
            'status' => 'want_to_read',
        ]);
    }

    public function test_store_requires_title(): void
    {
        $this->actingAsUser();

        $response = $this->post('/books', [
            'status' => 'want_to_read',
        ]);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_store_requires_valid_status(): void
    {
        $this->actingAsUser();

        $response = $this->post('/books', [
            'title' => 'Test Book',
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    public function test_user_can_view_own_book(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->get("/books/{$book->added_at->timestamp}");

        $response->assertStatus(200);
        $response->assertSee($book->title);
    }

    public function test_user_cannot_view_another_users_book(): void
    {
        $this->actingAsUser();
        $otherUser = $this->createUser();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get("/books/{$book->added_at->timestamp}");

        $response->assertStatus(404);
    }

    public function test_user_can_edit_own_book(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->get("/books/{$book->added_at->timestamp}/edit");

        $response->assertStatus(200);
    }

    public function test_user_cannot_edit_another_users_book(): void
    {
        $this->actingAsUser();
        $otherUser = $this->createUser();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get("/books/{$book->added_at->timestamp}/edit");

        $response->assertStatus(404);
    }

    public function test_user_can_update_own_book(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->put("/books/{$book->added_at->timestamp}", [
            'title' => 'Updated Title',
            'status' => 'currently_reading',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_update_another_users_book(): void
    {
        $this->actingAsUser();
        $otherUser = $this->createUser();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->put("/books/{$book->added_at->timestamp}", [
            'title' => 'Hacked Title',
            'status' => 'want_to_read',
        ]);

        $response->assertStatus(404);
    }

    public function test_user_can_delete_own_book(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->delete("/books/{$book->added_at->timestamp}");

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_user_cannot_delete_another_users_book(): void
    {
        $this->actingAsUser();
        $otherUser = $this->createUser();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->delete("/books/{$book->added_at->timestamp}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_book_route_binding_uses_timestamp(): void
    {
        $user = $this->actingAsUser();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $timestamp = $book->added_at->timestamp;
        $this->assertEquals($timestamp, $book->getRouteKey());

        $this->get("/books/{$timestamp}")->assertStatus(200);
    }
}
