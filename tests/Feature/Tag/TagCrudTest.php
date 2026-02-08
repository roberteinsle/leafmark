<?php

namespace Tests\Feature\Tag;

use App\Models\Book;
use App\Models\Tag;
use Tests\TestCase;

class TagCrudTest extends TestCase
{
    public function test_user_can_view_tags_index(): void
    {
        $user = $this->actingAsUser();
        Tag::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->get('/tags');

        $response->assertStatus(200);
    }

    public function test_user_can_create_tag(): void
    {
        $user = $this->actingAsUser();

        $response = $this->post('/tags', [
            'name' => 'Fiction',
            'color' => '#ff5733',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tags', [
            'user_id' => $user->id,
            'name' => 'Fiction',
            'color' => '#ff5733',
        ]);
    }

    public function test_tag_requires_valid_hex_color(): void
    {
        $this->actingAsUser();

        $this->post('/tags', [
            'name' => 'Test Tag',
            'color' => 'not-a-color',
        ])->assertSessionHasErrors(['color']);

        $this->post('/tags', [
            'name' => 'Test Tag',
            'color' => '#GGG',
        ])->assertSessionHasErrors(['color']);
    }

    public function test_user_can_update_own_tag(): void
    {
        $user = $this->actingAsUser();
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        $response = $this->patch("/tags/{$tag->id}", [
            'name' => 'Updated Tag',
            'color' => '#000000',
        ]);

        $response->assertRedirect();
        $tag->refresh();
        $this->assertEquals('Updated Tag', $tag->name);
    }

    public function test_user_cannot_update_another_users_tag(): void
    {
        $this->actingAsUser();
        $otherUser = $this->createUser();
        $tag = Tag::factory()->create(['user_id' => $otherUser->id]);

        $this->patch("/tags/{$tag->id}", [
            'name' => 'Hacked Tag',
            'color' => '#000000',
        ])->assertStatus(403);
    }

    public function test_user_can_delete_own_tag(): void
    {
        $user = $this->actingAsUser();
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        $this->delete("/tags/{$tag->id}")->assertRedirect();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_user_cannot_delete_default_tag(): void
    {
        $user = $this->actingAsUser();
        $tag = Tag::factory()->default()->create(['user_id' => $user->id]);

        $response = $this->delete("/tags/{$tag->id}");

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_user_can_add_book_to_tag(): void
    {
        $user = $this->actingAsUser();
        $tag = Tag::factory()->create(['user_id' => $user->id]);
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->post("/tags/{$tag->id}/books/{$book->id}");

        $response->assertRedirect();
        $this->assertTrue($tag->books()->where('book_id', $book->id)->exists());
    }

    public function test_user_can_remove_book_from_tag(): void
    {
        $user = $this->actingAsUser();
        $tag = Tag::factory()->create(['user_id' => $user->id]);
        $book = Book::factory()->create(['user_id' => $user->id]);
        $tag->addBook($book);

        $response = $this->delete("/tags/{$tag->id}/books/{$book->id}");

        $response->assertRedirect();
        $this->assertFalse($tag->books()->where('book_id', $book->id)->exists());
    }

    public function test_cannot_add_other_users_book_to_tag(): void
    {
        $user = $this->actingAsUser();
        $tag = Tag::factory()->create(['user_id' => $user->id]);
        $otherUser = $this->createUser();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);

        // Book route binding is user-scoped, so another user's book returns 404
        $this->post("/tags/{$tag->id}/books/{$book->id}")->assertStatus(404);
    }
}
