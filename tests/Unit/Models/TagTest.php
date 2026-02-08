<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Tag;
use Tests\TestCase;

class TagTest extends TestCase
{
    public function test_add_book_creates_pivot(): void
    {
        $user = $this->createUser();
        $tag = Tag::factory()->create(['user_id' => $user->id]);
        $book = Book::factory()->create(['user_id' => $user->id]);

        $tag->addBook($book);

        $this->assertTrue($tag->books()->where('book_id', $book->id)->exists());
    }

    public function test_add_book_does_not_duplicate(): void
    {
        $user = $this->createUser();
        $tag = Tag::factory()->create(['user_id' => $user->id]);
        $book = Book::factory()->create(['user_id' => $user->id]);

        $tag->addBook($book);
        $tag->addBook($book);

        $this->assertEquals(1, $tag->books()->count());
    }

    public function test_remove_book_detaches(): void
    {
        $user = $this->createUser();
        $tag = Tag::factory()->create(['user_id' => $user->id]);
        $book = Book::factory()->create(['user_id' => $user->id]);

        $tag->addBook($book);
        $tag->removeBook($book);

        $this->assertFalse($tag->books()->where('book_id', $book->id)->exists());
    }

    public function test_scopes(): void
    {
        $user = $this->createUser();
        Tag::factory()->default()->create(['user_id' => $user->id, 'name' => 'Default Tag']);
        Tag::factory()->create(['user_id' => $user->id, 'name' => 'Custom Tag']);

        $this->assertEquals(1, Tag::where('user_id', $user->id)->default()->count());
        $this->assertEquals(1, Tag::where('user_id', $user->id)->custom()->count());
    }
}
