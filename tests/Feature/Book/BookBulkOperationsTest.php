<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Tag;
use Tests\TestCase;

class BookBulkOperationsTest extends TestCase
{
    public function test_user_can_bulk_delete_own_books(): void
    {
        $user = $this->actingAsUser();
        $books = Book::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->post('/books/bulk-delete', [
            'book_ids' => $books->pluck('id')->toArray(),
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertEquals(0, $user->books()->count());
    }

    public function test_bulk_delete_scoped_to_user(): void
    {
        $user = $this->actingAsUser();
        $otherUser = $this->createUser();

        $myBook = Book::factory()->create(['user_id' => $user->id]);
        $otherBook = Book::factory()->create(['user_id' => $otherUser->id]);

        $this->post('/books/bulk-delete', [
            'book_ids' => [$myBook->id, $otherBook->id],
        ]);

        $this->assertDatabaseMissing('books', ['id' => $myBook->id]);
        $this->assertDatabaseHas('books', ['id' => $otherBook->id]);
    }

    public function test_user_can_bulk_add_tags(): void
    {
        $user = $this->actingAsUser();
        $books = Book::factory()->count(2)->create(['user_id' => $user->id]);
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        $response = $this->post('/books/bulk-add-tags', [
            'book_ids' => $books->pluck('id')->toArray(),
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect(route('books.index'));
        foreach ($books as $book) {
            $this->assertTrue($book->tags()->where('tag_id', $tag->id)->exists());
        }
    }

    public function test_user_can_bulk_remove_tag(): void
    {
        $user = $this->actingAsUser();
        $books = Book::factory()->count(2)->create(['user_id' => $user->id]);
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        foreach ($books as $book) {
            $book->tags()->attach($tag->id);
        }

        $response = $this->post('/books/bulk-remove-tag', [
            'book_ids' => $books->pluck('id')->toArray(),
            'tag_id' => $tag->id,
        ]);

        $response->assertRedirect(route('books.index'));
        foreach ($books as $book) {
            $this->assertFalse($book->tags()->where('tag_id', $tag->id)->exists());
        }
    }

    public function test_bulk_add_tags_rejects_other_users_tags(): void
    {
        $user = $this->actingAsUser();
        $otherUser = $this->createUser();

        $book = Book::factory()->create(['user_id' => $user->id]);
        $otherTag = Tag::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->post('/books/bulk-add-tags', [
            'book_ids' => [$book->id],
            'tag_ids' => [$otherTag->id],
        ]);

        $response->assertSessionHas('error');
    }
}
