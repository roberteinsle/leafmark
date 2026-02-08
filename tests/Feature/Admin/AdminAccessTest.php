<?php

namespace Tests\Feature\Admin;

use App\Models\SystemSetting;
use App\Models\User;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin')->assertStatus(200);
    }

    public function test_non_admin_gets_403(): void
    {
        $this->actingAsUser();

        $this->get('/admin')->assertStatus(403);
    }

    public function test_admin_can_view_users_list(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/users')->assertStatus(200);
    }

    public function test_admin_can_toggle_other_user_admin(): void
    {
        $this->actingAsAdmin();
        $user = $this->createUser();

        $this->assertFalse($user->is_admin);

        $this->patch("/admin/users/{$user->id}/toggle-admin");

        $user->refresh();
        $this->assertTrue($user->is_admin);
    }

    public function test_admin_cannot_toggle_own_admin(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->patch("/admin/users/{$admin->id}/toggle-admin");

        $response->assertRedirect();
        $admin->refresh();
        $this->assertTrue($admin->is_admin);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $this->actingAsAdmin();
        $user = $this->createUser();

        $this->delete("/admin/users/{$user->id}");

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->actingAsAdmin();

        $response = $this->delete("/admin/users/{$admin->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_update_registration_settings(): void
    {
        $this->actingAsAdmin();

        $this->patch('/admin/settings', [
            'registration_enabled' => true,
            'registration_mode' => 'code',
            'registration_code' => 'TESTCODE',
        ]);

        $this->assertEquals('code', SystemSetting::getRegistrationMode());
        $this->assertEquals('TESTCODE', SystemSetting::getRegistrationCode());
    }

    public function test_admin_can_edit_user(): void
    {
        $this->actingAsAdmin();
        $user = $this->createUser();

        $this->get("/admin/users/{$user->id}/edit")->assertStatus(200);
    }

    public function test_admin_can_update_user_details(): void
    {
        $this->actingAsAdmin();
        $user = $this->createUser();

        $this->patch("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
    }
}
