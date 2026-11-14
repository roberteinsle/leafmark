<?php

namespace Tests\Feature\Admin;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MailAndApiSettingsTest extends TestCase
{
    private function smtpPayload(array $overrides = []): array
    {
        return array_merge([
            'smtp_enabled' => '1',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => '587',
            'smtp_encryption' => 'tls',
            'smtp_username' => 'mailer',
            'smtp_password' => 'mail-secret',
            'smtp_from_address' => 'books@example.com',
            'smtp_from_name' => 'Leafmark',
        ], $overrides);
    }

    public function test_admin_can_save_smtp_settings_with_encrypted_password(): void
    {
        $this->actingAsAdmin();

        $this->patch(route('admin.settings.mail'), $this->smtpPayload())->assertSessionHasNoErrors();

        $this->assertTrue(SystemSetting::isSmtpEnabled());
        $config = SystemSetting::getSmtpConfig();
        $this->assertSame('smtp.example.com', $config['host']);
        $this->assertSame('mail-secret', $config['password']);
        $this->assertNotSame('mail-secret', DB::table('system_settings')->where('key', 'smtp_password')->value('value'));
    }

    public function test_empty_password_keeps_saved_one_and_clear_removes_it(): void
    {
        $this->actingAsAdmin();
        $this->patch(route('admin.settings.mail'), $this->smtpPayload());

        $this->patch(route('admin.settings.mail'), $this->smtpPayload(['smtp_password' => '']));
        $this->assertSame('mail-secret', SystemSetting::getSmtpConfig()['password']);

        $this->patch(route('admin.settings.mail'), $this->smtpPayload(['smtp_password' => '', 'smtp_password_clear' => '1']));
        $this->assertSame('', SystemSetting::getSmtpConfig()['password']);
    }

    public function test_password_is_never_rendered(): void
    {
        $this->actingAsAdmin();
        $this->patch(route('admin.settings.mail'), $this->smtpPayload());

        $this->get(route('admin.settings'))->assertOk()->assertDontSee('mail-secret');
    }

    public function test_enabling_smtp_requires_host_and_sender(): void
    {
        $this->actingAsAdmin();

        $this->patch(route('admin.settings.mail'), $this->smtpPayload(['smtp_host' => '', 'smtp_from_address' => '']))
            ->assertSessionHasErrors(['smtp_host', 'smtp_from_address']);
    }

    public function test_unchecked_smtp_disables_it(): void
    {
        $this->actingAsAdmin();
        $this->patch(route('admin.settings.mail'), $this->smtpPayload());

        $this->patch(route('admin.settings.mail'), $this->smtpPayload(['smtp_enabled' => '0']))->assertSessionHasNoErrors();

        $this->assertFalse(SystemSetting::isSmtpEnabled());
    }

    public function test_test_mail_requires_enabled_smtp(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.settings.mail.test'))->assertSessionHasErrors('smtp');
    }

    public function test_api_keys_are_stored_encrypted_and_hidden(): void
    {
        $this->actingAsAdmin();

        $this->patch(route('admin.settings.api-keys'), ['google_books_api_key' => 'AIza-test-key', 'bigbook_api_key' => 'bb-key'])
            ->assertSessionHasNoErrors();

        $this->assertSame('AIza-test-key', SystemSetting::getSecret('google_books_api_key'));
        $this->assertNotSame('AIza-test-key', DB::table('system_settings')->where('key', 'google_books_api_key')->value('value'));
        $this->get(route('admin.settings'))->assertDontSee('AIza-test-key')->assertDontSee('bb-key');
    }

    public function test_legacy_plaintext_secret_is_still_readable(): void
    {
        SystemSetting::set('bigbook_api_key', 'plain-legacy');

        $this->assertSame('plain-legacy', SystemSetting::getSecret('bigbook_api_key'));
    }

    public function test_google_key_test_reports_googles_answer(): void
    {
        $this->actingAsAdmin();
        SystemSetting::setSecret('google_books_api_key', 'AIza-test-key');

        Http::fake(['www.googleapis.com/*' => Http::sequence()
            ->push(['error' => ['message' => 'Permission denied on the service.', 'details' => [['reason' => 'API_KEY_IP_ADDRESS_BLOCKED']]]], 403)
            ->push(['totalItems' => 1, 'items' => []], 200)]);

        $this->post(route('admin.settings.api-keys.test-google'))->assertSessionHasErrors('google');
        $this->assertStringContainsString('API_KEY_IP_ADDRESS_BLOCKED', session('errors')->first('google'));
        $this->post(route('admin.settings.api-keys.test-google'))->assertSessionHas('success');
    }

    public function test_settings_require_admin(): void
    {
        $this->actingAsUser();

        $this->patch(route('admin.settings.mail'), $this->smtpPayload())->assertForbidden();
        $this->patch(route('admin.settings.api-keys'), ['google_books_api_key' => 'x'])->assertForbidden();
    }
}
