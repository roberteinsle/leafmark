<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\ReadingChallenge;
use Tests\TestCase;

class ReadingChallengeTest extends TestCase
{
    public function test_progress_counts_finished_books_for_year(): void
    {
        $user = $this->createUser();
        $challenge = ReadingChallenge::create([
            'user_id' => $user->id,
            'year' => now()->year,
            'goal' => 10,
        ]);

        Book::factory()->count(3)->read()->create([
            'user_id' => $user->id,
            'finished_at' => now(),
        ]);

        // Book from different year should not count
        Book::factory()->read()->create([
            'user_id' => $user->id,
            'finished_at' => now()->subYear(),
        ]);

        $this->assertEquals(3, $challenge->progress);
    }

    public function test_progress_percentage(): void
    {
        $user = $this->createUser();
        $challenge = ReadingChallenge::create([
            'user_id' => $user->id,
            'year' => now()->year,
            'goal' => 10,
        ]);

        Book::factory()->count(5)->read()->create([
            'user_id' => $user->id,
            'finished_at' => now(),
        ]);

        $this->assertEquals(50.0, $challenge->progress_percentage);
    }

    public function test_is_completed(): void
    {
        $user = $this->createUser();
        $challenge = ReadingChallenge::create([
            'user_id' => $user->id,
            'year' => now()->year,
            'goal' => 2,
        ]);

        $this->assertFalse($challenge->is_completed);

        Book::factory()->count(2)->read()->create([
            'user_id' => $user->id,
            'finished_at' => now(),
        ]);

        $this->assertTrue($challenge->is_completed);
    }
}
