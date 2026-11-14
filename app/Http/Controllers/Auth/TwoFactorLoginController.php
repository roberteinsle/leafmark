<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Zweiter Schritt nach korrektem Passwort: 2FA-Code abfragen oder 2FA erstmalig einrichten.
 * Die Session enthält bis dahin nur `login.id`, der Nutzer ist noch nicht angemeldet.
 */
class TwoFactorLoginController extends Controller
{
    public function __construct(private TwoFactorService $twoFactor)
    {
    }

    public function showChallenge(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user?->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function challenge(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user?->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:16'],
            'recovery_code' => ['nullable', 'string', 'max:32'],
        ]);

        $valid = filled($validated['code'] ?? null)
            ? $this->twoFactor->verify($user->two_factor_secret, $validated['code'], $user->id)
            : filled($validated['recovery_code'] ?? null) && $this->twoFactor->useRecoveryCode($user, $validated['recovery_code']);

        if (! $valid) {
            throw ValidationException::withMessages(['code' => __('app.two_factor.invalid_code')]);
        }

        return $this->completeLogin($request, $user);
    }

    public function showSetup(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        $secret = $request->session()->get('login.setup_secret') ?? $this->twoFactor->generateSecret();
        $request->session()->put('login.setup_secret', $secret);

        return view('auth.two-factor-setup', [
            'qrCode' => $this->twoFactor->qrCodeSvg($user, $secret),
            'secret' => $secret,
        ]);
    }

    public function setup(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        $secret = $request->session()->get('login.setup_secret');

        if (! $user || ! $secret || $user->hasTwoFactorEnabled()) {
            return redirect()->route('login');
        }

        $validated = $request->validate(['code' => ['required', 'string', 'max:16']]);

        if (! $this->twoFactor->verify($secret, $validated['code'], $user->id)) {
            throw ValidationException::withMessages(['code' => __('app.two_factor.invalid_code')]);
        }

        $codes = $this->twoFactor->enable($user, $secret);
        $request->session()->forget('login.setup_secret');

        return $this->completeLogin($request, $user, redirect()->route('settings.security')
            ->with('recovery_codes', $codes)
            ->with('success', __('app.two_factor.enabled')));
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('login.id');

        return $id ? User::find($id) : null;
    }

    private function completeLogin(Request $request, User $user, ?RedirectResponse $redirect = null): RedirectResponse
    {
        $remember = (bool) $request->session()->pull('login.remember', false);
        $request->session()->forget('login.id');

        Auth::login($user, $remember);
        $request->session()->regenerate();
        // Frische Anmeldung mit zwei Faktoren: Passwort gilt als bestätigt (Sicherheitsseite ohne erneute Abfrage)
        $request->session()->passwordConfirmed();

        return $redirect ?? redirect()->intended(route('books.index'));
    }
}
