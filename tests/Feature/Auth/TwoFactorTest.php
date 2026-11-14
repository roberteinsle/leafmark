<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TwoFactorService;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/**
 * Standard Abschnitt 11: Passwort-Login nur zusammen mit 2FA.
 */
class TwoFactorTest extends TestCase
{
    private function otp(string $secret): string
    {
        return app(Google2FA::class)->getCurrentOtp($secret);
    }

    private function userWithTwoFactor(): array
    {
        $user = $this->createUser(['password' => bcrypt('password123')]);
        $secret = app(TwoFactorService::class)->generateSecret();
        $codes = app(TwoFactorService::class)->enable($user, $secret);

        return [$user->fresh(), $secret, $codes];
    }

    private function submitPassword(User $user): void
    {
        $this->post('/login', ['email' => $user->email, 'password' => 'password123']);
    }

    public function test_first_password_login_requires_setting_up_two_factor(): void
    {
        $user = $this->createUser(['password' => bcrypt('password123')]);
        $this->submitPassword($user);

        $this->get(route('two-factor.setup'))->assertOk()->assertSee('<svg', false);
        $this->assertGuest();

        $secret = session('login.setup_secret');
        $this->post(route('two-factor.setup.submit'), ['code' => $this->otp($secret)])
            ->assertRedirect(route('settings.security'))
            ->assertSessionHas('recovery_codes');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_setup_rejects_wrong_code(): void
    {
        $user = $this->createUser(['password' => bcrypt('password123')]);
        $this->submitPassword($user);
        $this->get(route('two-factor.setup'));

        $this->post(route('two-factor.setup.submit'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_password_login_with_two_factor_requires_code(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();

        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        $this->post(route('two-factor.challenge.submit'), ['code' => $this->otp($secret)])
            ->assertRedirect(route('books.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_code_is_rejected(): void
    {
        [$user] = $this->userWithTwoFactor();
        $this->submitPassword($user);

        $this->post(route('two-factor.challenge.submit'), ['code' => '123456'])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_code_cannot_be_replayed(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();
        $code = $this->otp($secret);

        $this->submitPassword($user);
        $this->post(route('two-factor.challenge.submit'), ['code' => $code]);
        $this->post(route('logout'));

        $this->submitPassword($user);
        $this->post(route('two-factor.challenge.submit'), ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_recovery_code_works_once(): void
    {
        [$user, , $codes] = $this->userWithTwoFactor();

        $this->submitPassword($user);
        $this->post(route('two-factor.challenge.submit'), ['recovery_code' => $codes[0]])->assertRedirect(route('books.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertCount(count($codes) - 1, $user->fresh()->two_factor_recovery_codes);
        $this->post(route('logout'));

        $this->submitPassword($user);
        $this->post(route('two-factor.challenge.submit'), ['recovery_code' => $codes[0]])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_challenge_without_password_step_redirects_to_login(): void
    {
        $this->get(route('two-factor.challenge'))->assertRedirect(route('login'));
        $this->get(route('two-factor.setup'))->assertRedirect(route('login'));
        $this->post(route('two-factor.challenge.submit'), ['code' => '123456'])->assertRedirect(route('login'));
    }

    public function test_secret_is_stored_encrypted(): void
    {
        [$user, $secret] = $this->userWithTwoFactor();

        $raw = \DB::table('users')->where('id', $user->id)->value('two_factor_secret');

        $this->assertNotSame($secret, $raw);
        $this->assertSame($secret, $user->two_factor_secret);
    }

    public function test_admin_can_reset_two_factor(): void
    {
        [$user] = $this->userWithTwoFactor();
        $this->actingAsAdmin();

        $this->patch(route('admin.users.reset-two-factor', $user))->assertSessionHas('success');

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_non_admin_cannot_reset_two_factor(): void
    {
        [$user] = $this->userWithTwoFactor();
        $this->actingAsUser();

        $this->patch(route('admin.users.reset-two-factor', $user))->assertForbidden();
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }
}
