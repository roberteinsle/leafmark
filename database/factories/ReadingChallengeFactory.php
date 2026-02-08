<?php

namespace Database\Factories;

use App\Models\ReadingChallenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingChallengeFactory extends Factory
{
    protected $model = ReadingChallenge::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'year' => now()->year,
            'goal' => fake()->numberBetween(5, 50),
        ];
    }
}
