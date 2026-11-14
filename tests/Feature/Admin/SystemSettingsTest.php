<?php

namespace Tests\Feature\Admin;

use App\Models\SystemSetting;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    public function test_unchecked_checkbox_disables_registration(): void
    {
        $this->actingAsAdmin();
        SystemSetting::set('registration_enabled', 'true');

        // Formular wie im Browser: Hidden-Feld "0", Checkbox nicht angehakt
        $this->patch(route('admin.settings.update'), [
            'registration_enabled' => '0',
            'registration_mode' => 'open',
        ])->assertSessionHasNoErrors();

        $this->assertFalse(SystemSetting::isRegistrationEnabled());
        $this->get(route('admin.settings'))->assertDontSee('name="registration_enabled" value="1" checked', false);
    }

    public function test_missing_field_also_disables_registration(): void
    {
        $this->actingAsAdmin();

        $this->patch(route('admin.settings.update'), ['registration_mode' => 'open'])->assertSessionHasNoErrors();

        $this->assertFalse(SystemSetting::isRegistrationEnabled());
    }

    public function test_checked_checkbox_enables_registration(): void
    {
        $this->actingAsAdmin();
        SystemSetting::set('registration_enabled', 'false');

        $this->patch(route('admin.settings.update'), [
            'registration_enabled' => '1',
            'registration_mode' => 'code',
            'registration_code' => 'secret-code',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(SystemSetting::isRegistrationEnabled());
        $this->assertSame('code', SystemSetting::getRegistrationMode());
    }

    public function test_disabled_registration_blocks_register_page(): void
    {
        SystemSetting::set('registration_enabled', 'false');

        $response = $this->post(route('register.submit'), [
            'name' => 'New',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }
}
