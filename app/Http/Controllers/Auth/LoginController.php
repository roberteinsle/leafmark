<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Passwort-Login (Standard Abschnitt 11, Rang 3): nur zusammen mit 2FA.
 *
 * Ein korrektes Passwort meldet noch nicht an. Es folgt die 2FA-Abfrage oder,
 * wenn noch keine 2FA eingerichtet ist, das Einrichten. Siehe TwoFactorLoginController.
 */
class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        if (! Auth::validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('app.login.failed'),
            ]);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();

        $request->session()->put('login.id', $user->id);
        $request->session()->put('login.remember', $request->boolean('remember'));
        $request->session()->regenerate();

        return $user->hasTwoFactorEnabled()
            ? redirect()->route('two-factor.challenge')
            : redirect()->route('two-factor.setup');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
