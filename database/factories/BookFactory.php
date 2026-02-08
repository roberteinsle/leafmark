<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'isbn' => fake()->isbn10(),
            'isbn13' => fake()->isbn13(),
            'publisher' => fake()->company(),
            'description' => fake()->paragraph(),
            'page_count' => fake()->numberBetween(100, 800),
            'current_page' => 0,
            'status' => 'want_to_read',
            'added_at' => now(),
            'language' => 'en',
        ];
    }

    public function currentlyReading(): static
    {
        return $this->state(fn () => [
            'status' => 'currently_reading',
            'started_at' => now(),
            'current_page' => fake()->numberBetween(1, 200),
        ]);
    }

    public function read(): static
    {
        return $this->state(function (array $attributes) {
            $pageCount = $attributes['page_count'] ?? 300;
            return [
                'status' => 'read',
                'started_at' => now()->subDays(30),
                'finished_at' => now(),
                'current_page' => $pageCount,
            ];
        });
    }

    public function withSeries(string $series = null, int $position = 1): static
    {
        return $this->state(fn () => [
            'series' => $series ?? fake()->sentence(2),
            'series_position' => $position,
        ]);
    }
}
