<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MagicLinkMail;
use App\Models\LoginToken;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * Anmeldung per Magic Link (Standard Abschnitt 11, Rang 2).
 *
 * Ablauf: Adresse eingeben → Mail mit signiertem Link (15 Minuten) → Bestätigungsseite → Anmeldung.
 * Die Bestätigungsseite verhindert, dass Link-Scanner in Mailprogrammen das Einmal-Token verbrauchen.
 */
class MagicLinkController extends Controller
{
    public function showRequestForm(): View
    {
        abort_unless(SystemSetting::isSmtpEnabled(), 404);

        return view('auth.magic-link');
    }

    public function send(Request $request): RedirectResponse
    {
        abort_unless(SystemSetting::isSmtpEnabled(), 404);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $token = LoginToken::issueFor($user);
            $url = URL::temporarySignedRoute(
                'login.magic.confirm',
                now()->addMinutes(LoginToken::LIFETIME_MINUTES),
                ['token' => $token]
            );

            try {
                Mail::to($user->email)->send(new MagicLinkMail($url));
            } catch (\Throwable $e) {
                Log::error('Magic link email failed: '.$e->getMessage());
            }
        }

        // Immer dieselbe Antwort, damit sich nicht prüfen lässt, welche Konten existieren
        return back()->with('status', __('app.magic_link.sent'));
    }

    /**
     * GET aus der E-Mail: Signatur und Token prüfen, aber noch nicht einlösen.
     */
    public function confirm(Request $request, string $token): View|RedirectResponse
    {
        if (! LoginToken::findValid($token)) {
            return redirect()->route('login')->withErrors(['email' => __('app.magic_link.invalid')]);
        }

        return view('auth.magic-link-confirm', ['token' => $token]);
    }

    /**
     * POST von der Bestätigungsseite: Token einlösen (nur einmal gültig) und anmelden.
     */
    public function consume(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $user = LoginToken::consume($validated['token']);

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => __('app.magic_link.invalid')]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();
        // Frische Anmeldung über das Postfach: gilt als Passwort-Bestätigung (Passkey direkt einrichtbar)
        $request->session()->passwordConfirmed();

        // Wer den Link aus dem Postfach öffnet, hat die Adresse bestätigt
        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        // Standard: Nach der ersten Anmeldung per Magic Link einen Passkey anbieten (einmalig)
        if (! $user->passkey_prompted_at && ! $user->passkeys()->exists()) {
            $user->forceFill(['passkey_prompted_at' => now()])->save();

            return redirect()->route('settings.security')->with('offer_passkey', true);
        }

        return redirect()->intended(route('books.index'));
    }
}
