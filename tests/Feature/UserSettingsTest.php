<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    public function test_user_can_view_settings(): void
    {
        $this->actingAsUser();

        $this->get('/settings')->assertStatus(200);
    }

    public function test_user_can_update_name_and_email(): void
    {
        $user = $this->actingAsUser();

        $response = $this->patch('/settings', [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'preferred_language' => 'en',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
    }

    public function test_user_can_change_password(): void
    {
        $user = $this->actingAsUser(['password' => Hash::make('oldpassword')]);

        $response = $this->patch('/settings', [
            'name' => $user->name,
            'email' => $user->email,
            'preferred_language' => 'en',
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = $this->actingAsUser();

        $response = $this->patch('/settings', [
            'name' => $user->name,
            'email' => $user->email,
            'preferred_language' => 'en',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_wrong_current_password_rejected(): void
    {
        $user = $this->actingAsUser(['password' => Hash::make('correctpassword')]);

        $response = $this->patch('/settings', [
            'name' => $user->name,
            'email' => $user->email,
            'preferred_language' => 'en',
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }
}
