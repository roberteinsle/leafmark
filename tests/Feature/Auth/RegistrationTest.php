<?php

namespace Tests\Feature\Auth;

use App\Models\SystemSetting;
use App\Models\User;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_registration_page_loads(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertAuthenticated();
    }

    public function test_first_user_becomes_admin(): void
    {
        $this->assertSame(0, User::count());

        $this->post('/register', [
            'name' => 'First User',
            'email' => 'first@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::first();
        $this->assertTrue($user->is_admin);
    }

    public function test_second_user_is_not_admin(): void
    {
        $this->createUser();

        $this->post('/register', [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'second@example.com')->first();
        $this->assertFalse($user->is_admin);
    }

    public function test_registration_fails_with_invalid_data(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        $this->createUser(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_registration_disabled_returns_403(): void
    {
        SystemSetting::set('registration_enabled', 'false');

        $this->get('/register')->assertStatus(403);
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(403);
    }

    public function test_domain_mode_rejects_wrong_domain(): void
    {
        SystemSetting::set('registration_mode', 'domain');
        SystemSetting::set('allowed_email_domains', 'example.com');

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@otherdomain.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseMissing('users', ['email' => 'test@otherdomain.com']);
    }

    public function test_domain_mode_accepts_correct_domain(): void
    {
        SystemSetting::set('registration_mode', 'domain');
        SystemSetting::set('allowed_email_domains', 'example.com');

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_code_mode_rejects_wrong_code(): void
    {
        SystemSetting::set('registration_mode', 'code');
        SystemSetting::set('registration_code', 'SECRET123');

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'WRONGCODE',
        ]);

        $response->assertSessionHasErrors(['registration_code']);
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_code_mode_accepts_correct_code(): void
    {
        SystemSetting::set('registration_mode', 'code');
        SystemSetting::set('registration_code', 'SECRET123');

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'registration_code' => 'SECRET123',
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_authenticated_user_redirected_from_register(): void
    {
        $this->actingAsUser();

        $this->get('/register')->assertRedirect();
    }
}
