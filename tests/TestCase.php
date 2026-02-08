<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function createUser(array $overrides = []): User
    {
        return User::factory()->create($overrides);
    }

    protected function createAdmin(array $overrides = []): User
    {
        return User::factory()->admin()->create($overrides);
    }

    protected function actingAsUser(array $overrides = []): User
    {
        $user = $this->createUser($overrides);
        $this->actingAs($user);
        return $user;
    }

    protected function actingAsAdmin(array $overrides = []): User
    {
        $user = $this->createAdmin($overrides);
        $this->actingAs($user);
        return $user;
    }
}
