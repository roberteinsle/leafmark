<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingChallenge;
use Tests\TestCase;

class StatsTest extends TestCase
{
    public function test_guest_cannot_access_stats(): void
    {
        $this->get('/stats')->assertRedirect('/login');
    }

    public function test_user_can_view_stats_page(): void
    {
        $this->actingAsUser();
        $this->get('/stats')->assertStatus(200);
    }

    public function test_stats_shows_correct_book_count(): void
    {
        $user = $this->actingAsUser();

        Book::factory()->count(5)->read()->create([
            'user_id' => $user->id,
            'finished_at' => now(),
        ]);

        Book::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'want_to_read',
        ]);

        $response = $this->get('/stats');
        $response->assertStatus(200);
        $response->assertViewHas('basicStats', function ($stats) {
            return $stats['total_books_read'] === 5;
        });
    }

    public function test_stats_calculates_total_pages(): void
    {
        $user = $this->actingAsUser();

        Book::factory()->read()->create([
            'user_id' => $user->id,
            'page_count' => 200,
            'finished_at' => now(),
        ]);
        Book::factory()->read()->create([
            'user_id' => $user->id,
            'page_count' => 300,
            'finished_at' => now(),
        ]);

        $response = $this->get('/stats');
        $response->assertViewHas('basicStats', function ($stats) {
            return $stats['total_pages_read'] === 500;
        });
    }

    public function test_stats_scoped_to_authenticated_user(): void
    {
        $user = $this->actingAsUser();
        $otherUser = $this->createUser();

        Book::factory()->count(3)->read()->create([
            'user_id' => $user->id,
            'finished_at' => now(),
        ]);
        Book::factory()->count(10)->read()->create([
            'user_id' => $otherUser->id,
            'finished_at' => now(),
        ]);

        $response = $this->get('/stats');
        $response->assertViewHas('basicStats', function ($stats) {
            return $stats['total_books_read'] === 3;
        });
    }

    public function test_year_filter_works(): void
    {
        $user = $this->actingAsUser();

        Book::factory()->count(2)->read()->create([
            'user_id' => $user->id,
            'finished_at' => now(),
        ]);
        Book::factory()->count(5)->read()->create([
            'user_id' => $user->id,
            'finished_at' => now()->subYear(),
        ]);

        $response = $this->get('/stats?year=' . now()->year);
        $response->assertViewHas('basicStats', function ($stats) {
            return $stats['books_read_this_year'] === 2;
        });
    }

    public function test_empty_state_works(): void
    {
        $this->actingAsUser();

        $response = $this->get('/stats');
        $response->assertStatus(200);
        $response->assertViewHas('basicStats', function ($stats) {
            return $stats['total_books_read'] === 0
                && $stats['total_pages_read'] === 0
                && $stats['avg_rating'] === null;
        });
    }

    public function test_challenge_data_passed_to_view(): void
    {
        $user = $this->actingAsUser();

        ReadingChallenge::create([
            'user_id' => $user->id,
            'year' => now()->year,
            'goal' => 24,
        ]);

        $response = $this->get('/stats');
        $response->assertViewHas('challenge');
    }
}
