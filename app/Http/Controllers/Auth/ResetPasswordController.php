<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /**
     * Show the password reset form
     */
    public function showResetForm(Request $request, $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Handle password reset
     */
    public function reset(Request $request): RedirectResponse
    {
        $rules = [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];

        // Add Turnstile validation if enabled
        if (SystemSetting::isTurnstileEnabled()) {
            $rules['cf-turnstile-response'] = ['required', new \App\Rules\TurnstileValid()];
        }

        $request->validate($rules);

        // Find the password reset token
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetToken) {
            return back()->withErrors(['email' => __('app.password_reset.invalid_token')]);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($resetToken->created_at) > 60) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return back()->withErrors(['email' => __('app.password_reset.invalid_token')]);
        }

        // Verify the token
        if (!Hash::check($request->token, $resetToken->token)) {
            return back()->withErrors(['email' => __('app.password_reset.invalid_token')]);
        }

        // Find the user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => __('app.password_reset.invalid_token')]);
        }

        // Update the password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return redirect()->route('login.' . app()->getLocale())->with('status', __('app.password_reset.success'));
    }
}
