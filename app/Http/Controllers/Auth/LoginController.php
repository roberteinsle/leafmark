<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];

        // Add Turnstile validation if enabled
        if (\App\Models\SystemSetting::isTurnstileEnabled()) {
            $rules['cf-turnstile-response'] = ['required', new \App\Rules\TurnstileValid()];
        }

        $credentials = $request->validate($rules);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            // Check if email is verified
            $user = Auth::user();

            if (!$user->email_verified_at) {
                Auth::logout();

                return back()->withErrors([
                    'email' => __('app.email_verification.not_verified'),
                ])->with('email', $credentials['email'])->with('show_resend', true);
            }

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            $request->session()->regenerate();

            return redirect()->intended(route('books.index'));
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
