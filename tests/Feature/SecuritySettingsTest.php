<?php

namespace Tests\Feature;

use App\Services\TwoFactorService;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class SecuritySettingsTest extends TestCase
{
    public function test_requires_password_confirmation(): void
    {
        $this->actingAsUser();

        $this->get(route('settings.security'))->assertRedirect(route('password.confirm'));
    }

    public function test_password_confirmation_unlocks_page(): void
    {
        $this->actingAsUser(['password' => bcrypt('password123')]);
        $this->get(route('settings.security'));

        $this->post(route('password.confirm.submit'), ['password' => 'wrong'])->assertSessionHasErrors('password');
        $this->post(route('password.confirm.submit'), ['password' => 'password123'])->assertRedirect(route('settings.security'));

        $this->get(route('settings.security'))
            ->assertOk()
            ->assertSee(__('app.passkeys.title'))
            ->assertSee(__('app.two_factor.title'));
    }

    public function test_passkey_registration_options_require_confirmation(): void
    {
        $this->actingAsUser();

        $this->getJson('/user/passkeys/options')->assertStatus(423);

        $this->withSession(['auth.password_confirmed_at' => time()])
            ->getJson('/user/passkeys/options')
            ->assertOk()
            ->assertJsonStructure(['options' => ['challenge', 'rp', 'user']]);
    }

    public function test_passkey_login_options_available_for_guests(): void
    {
        $this->getJson('/passkeys/login/options')->assertOk()->assertJsonStructure(['options' => ['challenge']]);
    }

    public function test_login_page_offers_passkey_first(): void
    {
        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringContainsString(__('app.login.with_passkey'), $html);
        $this->assertStringContainsString('autocomplete="email webauthn"', $html);
        $this->assertLessThan(
            strpos($html, __('app.login.or_password')),
            strpos($html, __('app.login.with_passkey')),
        );
    }

    public function test_enable_and_disable_two_factor_in_settings(): void
    {
        $user = $this->actingAsUser();
        $this->withSession(['auth.password_confirmed_at' => time()]);

        $this->post(route('settings.two-factor.start'))->assertRedirect(route('settings.security'));
        $secret = session('two_factor.setup_secret');
        $this->get(route('settings.security'))->assertSee('<svg', false);

        $this->post(route('settings.two-factor.confirm'), ['code' => app(Google2FA::class)->getCurrentOtp($secret)])
            ->assertSessionHas('recovery_codes');
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        $this->delete(route('settings.two-factor.disable'));
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_regenerate_recovery_codes_replaces_old_ones(): void
    {
        $user = $this->actingAsUser();
        $old = app(TwoFactorService::class)->enable($user, app(TwoFactorService::class)->generateSecret());

        $this->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('settings.two-factor.recovery-codes'))
            ->assertSessionHas('recovery_codes');

        $this->assertEmpty(array_intersect($old, $user->fresh()->two_factor_recovery_codes));
    }
}
