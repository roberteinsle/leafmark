<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Ziel der Middleware `password.confirm` (Laravel RequirePassword), u. a. für die Passkey-Verwaltung.
 */
class ConfirmPasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'max:255', 'current_password'],
        ]);

        $request->session()->passwordConfirmed();

        return redirect()->intended(route('settings.security'));
    }
}
