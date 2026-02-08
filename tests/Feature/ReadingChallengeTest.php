<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingChallenge;
use Tests\TestCase;

class ReadingChallengeTest extends TestCase
{
    public function test_user_can_view_challenge_page(): void
    {
        $this->actingAsUser();

        $this->get('/challenge')->assertStatus(200);
    }

    public function test_user_can_create_challenge(): void
    {
        $user = $this->actingAsUser();

        $response = $this->post('/challenge', [
            'goal' => 24,
            'year' => now()->year,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reading_challenges', [
            'user_id' => $user->id,
            'goal' => 24,
            'year' => now()->year,
        ]);
    }

    public function test_cannot_create_duplicate_year_challenge(): void
    {
        $user = $this->actingAsUser();
        ReadingChallenge::create([
            'user_id' => $user->id,
            'year' => now()->year,
            'goal' => 10,
        ]);

        $response = $this->post('/challenge', [
            'goal' => 20,
            'year' => now()->year,
        ]);

        $this->assertEquals(1, $user->readingChallenges()->where('year', now()->year)->count());
    }

    public function test_user_can_update_own_challenge(): void
    {
        $user = $this->actingAsUser();
        $challenge = ReadingChallenge::create([
            'user_id' => $user->id,
            'year' => now()->year,
            'goal' => 10,
        ]);

        $this->patch("/challenge/{$challenge->id}", [
            'goal' => 30,
        ]);

        $challenge->refresh();
        $this->assertEquals(30, $challenge->goal);
    }

    public function test_user_cannot_update_another_users_challenge(): void
    {
        $this->actingAsUser();
        $otherUser = $this->createUser();
        $challenge = ReadingChallenge::create([
            'user_id' => $otherUser->id,
            'year' => now()->year,
            'goal' => 10,
        ]);

        $this->patch("/challenge/{$challenge->id}", [
            'goal' => 99,
        ])->assertStatus(403);
    }

    public function test_user_can_delete_own_challenge(): void
    {
        $user = $this->actingAsUser();
        $challenge = ReadingChallenge::create([
            'user_id' => $user->id,
            'year' => now()->year,
            'goal' => 10,
        ]);

        $this->delete("/challenge/{$challenge->id}");

        $this->assertDatabaseMissing('reading_challenges', ['id' => $challenge->id]);
    }

    public function test_challenge_progress_counts_finished_books(): void
    {
        $user = $this->actingAsUser();
        $challenge = ReadingChallenge::create([
            'user_id' => $user->id,
            'year' => now()->year,
            'goal' => 10,
        ]);

        Book::factory()->count(3)->read()->create([
            'user_id' => $user->id,
            'finished_at' => now(),
        ]);

        Book::factory()->create([
            'user_id' => $user->id,
            'status' => 'currently_reading',
        ]);

        $this->assertEquals(3, $challenge->progress);
        $this->assertEquals(30.0, $challenge->progress_percentage);
    }
}
