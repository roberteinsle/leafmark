<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function test_guest_cannot_access_books(): void
    {
        $this->get('/books')->assertRedirect('/login');
    }

    public function test_guest_cannot_access_tags(): void
    {
        $this->get('/tags')->assertRedirect('/login');
    }

    public function test_guest_cannot_access_settings(): void
    {
        $this->get('/settings')->assertRedirect('/login');
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_admin(): void
    {
        $this->actingAsUser();

        $this->get('/admin')->assertStatus(403);
    }
}
