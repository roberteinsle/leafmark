<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request): View
    {
        // Check if registration is enabled
        if (!SystemSetting::isRegistrationEnabled()) {
            abort(403, 'Registration is currently disabled.');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        // Check if registration is enabled
        if (!SystemSetting::isRegistrationEnabled()) {
            abort(403, 'Registration is currently disabled.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Check registration mode
        $registrationMode = SystemSetting::getRegistrationMode();

        if ($registrationMode === 'domain') {
            // Check if email domain is allowed
            if (!SystemSetting::isEmailDomainAllowed($request->email)) {
                return back()->withErrors([
                    'email' => 'Email domain is not allowed for registration.',
                ])->withInput();
            }
        }

        if ($registrationMode === 'code') {
            // Check registration code
            $request->validate([
                'registration_code' => ['required', 'string'],
            ]);

            if ($request->registration_code !== SystemSetting::getRegistrationCode()) {
                return back()->withErrors([
                    'registration_code' => 'Invalid registration code.',
                ])->withInput();
            }
        }

        // Check if this is the first user (will become admin)
        $isFirstUser = User::count() === 0;

        // Check if email matches ADMIN_EMAIL from .env
        $adminEmail = env('ADMIN_EMAIL');
        $isAdminEmail = !empty($adminEmail) && $request->email === $adminEmail;

        // User becomes admin if: first user OR email matches ADMIN_EMAIL
        $isAdmin = $isFirstUser || $isAdminEmail;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(), // Auto-verify email
            'is_admin' => $isAdmin,
        ]);

        event(new Registered($user));

        // Log in the user automatically
        Auth::login($user);

        return redirect()->route('books.index')->with('success', 'Welcome to Leafmark!');
    }
}
