<?php

namespace Tests\Feature\Auth;

use App\Mail\MagicLinkMail;
use App\Models\LoginToken;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MagicLinkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        SystemSetting::set('smtp_enabled', 'true');
    }

    private function requestLinkFor(string $email): ?string
    {
        $this->post(route('login.magic.send'), ['email' => $email]);

        $url = null;
        Mail::assertSent(MagicLinkMail::class, function (MagicLinkMail $mail) use (&$url) {
            $url = $mail->url;

            return true;
        });

        return $url;
    }

    private function tokenFromUrl(string $url): string
    {
        preg_match('#/login/magic/([A-Za-z0-9]{64})#', $url, $m);

        return $m[1];
    }

    public function test_hidden_when_smtp_disabled(): void
    {
        SystemSetting::set('smtp_enabled', 'false');

        $this->get(route('login.magic'))->assertNotFound();
        $this->get(route('login'))->assertDontSee(route('login.magic'));
    }

    public function test_login_page_offers_magic_link_when_smtp_enabled(): void
    {
        $this->get(route('login'))->assertSee(route('login.magic'));
    }

    public function test_full_flow_signs_user_in(): void
    {
        $user = $this->createUser(['email_verified_at' => null]);
        $url = $this->requestLinkFor($user->email);

        // GET aus der Mail zeigt nur die Bestätigungsseite und löst das Token noch nicht ein
        $this->get($url)->assertOk()->assertSee(__('app.magic_link.confirm_button'));
        $this->assertGuest();
        $this->assertSame(1, LoginToken::count());

        // Erste Anmeldung per Magic Link: Passkey wird einmalig angeboten
        $this->post(route('login.magic.consume'), ['token' => $this->tokenFromUrl($url)])
            ->assertRedirect(route('settings.security'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('settings.security'))->assertOk()->assertSee(__('app.passkeys.offer_title'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_passkey_offer_only_once(): void
    {
        $user = $this->createUser();

        $this->post(route('login.magic.consume'), ['token' => $this->tokenFromUrl($this->requestLinkFor($user->email))]);
        $this->post(route('logout'));

        $this->post(route('login.magic.consume'), ['token' => $this->tokenFromUrl($this->requestLinkFor($user->email))])
            ->assertRedirect(route('books.index'));
    }

    public function test_token_works_only_once(): void
    {
        $user = $this->createUser();
        $token = $this->tokenFromUrl($this->requestLinkFor($user->email));

        $this->post(route('login.magic.consume'), ['token' => $token]);
        $this->post(route('logout'));

        $this->post(route('login.magic.consume'), ['token' => $token])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_link_expires_after_15_minutes(): void
    {
        $user = $this->createUser();
        $url = $this->requestLinkFor($user->email);

        $this->travel(16)->minutes();

        $this->get($url)->assertForbidden(); // Signatur abgelaufen
        $this->post(route('login.magic.consume'), ['token' => $this->tokenFromUrl($url)])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unsigned_link_is_rejected(): void
    {
        $user = $this->createUser();
        $token = $this->tokenFromUrl($this->requestLinkFor($user->email));

        $this->get('/login/magic/'.$token)->assertForbidden();
    }

    public function test_response_is_identical_for_unknown_address(): void
    {
        $user = $this->createUser();

        $known = $this->from(route('login.magic'))->post(route('login.magic.send'), ['email' => $user->email]);
        $unknown = $this->from(route('login.magic'))->post(route('login.magic.send'), ['email' => 'nobody@example.com']);

        $known->assertRedirect(route('login.magic'))->assertSessionHas('status', __('app.magic_link.sent'));
        $unknown->assertRedirect(route('login.magic'))->assertSessionHas('status', __('app.magic_link.sent'));
        Mail::assertSentCount(1);
    }

    public function test_new_request_invalidates_older_link(): void
    {
        $user = $this->createUser();
        $first = $this->tokenFromUrl($this->requestLinkFor($user->email));
        $this->post(route('login.magic.send'), ['email' => $user->email]);

        $this->post(route('login.magic.consume'), ['token' => $first])->assertSessionHasErrors('email');
        $this->assertSame(1, LoginToken::count());
    }

    public function test_requests_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.magic.send'), ['email' => 'someone@example.com']);
        }

        $this->post(route('login.magic.send'), ['email' => 'someone@example.com'])->assertTooManyRequests();
    }

    public function test_signed_url_helper_matches_route(): void
    {
        $url = URL::temporarySignedRoute('login.magic.confirm', now()->addMinutes(15), ['token' => str_repeat('a', 64)]);

        $this->assertStringContainsString('/login/magic/'.str_repeat('a', 64), $url);
    }
}
