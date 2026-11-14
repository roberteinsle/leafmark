<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Einstellungen → Anmeldung & Sicherheit: Passkeys und Zwei-Faktor.
 * Route ist durch `password.confirm` geschützt.
 */
class SecuritySettingsController extends Controller
{
    public function __construct(private TwoFactorService $twoFactor)
    {
    }

    public function show(Request $request): View
    {
        $user = $request->user();

        // Angebot „Passkey einrichten“ nur einmal zeigen
        $offerPasskey = $request->session()->pull('offer_passkey', false);

        $setupSecret = $request->session()->get('two_factor.setup_secret');

        return view('settings.security', [
            'user' => $user,
            'passkeys' => $user->passkeys()->latest()->get(),
            'offerPasskey' => $offerPasskey,
            'setupSecret' => $setupSecret,
            'qrCode' => $setupSecret ? $this->twoFactor->qrCodeSvg($user, $setupSecret) : null,
            'recoveryCodes' => $request->session()->get('recovery_codes'),
        ]);
    }

    public function startTwoFactor(Request $request): RedirectResponse
    {
        abort_if($request->user()->hasTwoFactorEnabled(), 409);

        $request->session()->put('two_factor.setup_secret', $this->twoFactor->generateSecret());

        return redirect()->route('settings.security');
    }

    public function confirmTwoFactor(Request $request): RedirectResponse
    {
        $user = $request->user();
        $secret = $request->session()->get('two_factor.setup_secret');

        abort_if(! $secret || $user->hasTwoFactorEnabled(), 409);

        $validated = $request->validate(['code' => ['required', 'string', 'max:16']]);

        if (! $this->twoFactor->verify($secret, $validated['code'], $user->id)) {
            throw ValidationException::withMessages(['code' => __('app.two_factor.invalid_code')]);
        }

        $codes = $this->twoFactor->enable($user, $secret);
        $request->session()->forget('two_factor.setup_secret');

        return redirect()->route('settings.security')
            ->with('recovery_codes', $codes)
            ->with('success', __('app.two_factor.enabled'));
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->hasTwoFactorEnabled(), 409);

        $codes = $this->twoFactor->generateRecoveryCodes();
        $user->forceFill(['two_factor_recovery_codes' => $codes])->save();

        return redirect()->route('settings.security')->with('recovery_codes', $codes);
    }

    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $this->twoFactor->disable($request->user());
        $request->session()->forget('two_factor.setup_secret');

        return redirect()->route('settings.security')->with('success', __('app.two_factor.disabled'));
    }
}
