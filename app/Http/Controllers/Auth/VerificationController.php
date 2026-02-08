<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailMail;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

class VerificationController extends Controller
{
    /**
     * Handle email verification
     */
    public function verify(Request $request): RedirectResponse
    {
        // Validate the signed URL
        if (!$request->hasValidSignature()) {
            return redirect()->route('login')->withErrors(['email' => __('app.email_verification.invalid_link')]);
        }

        $user = User::find($request->route('id'));

        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => __('app.email_verification.invalid_link')]);
        }

        // Check if already verified
        if ($user->email_verified_at) {
            return redirect()->route('login')->with('status', __('app.email_verification.already_verified'));
        }

        // Verify the email hash
        if (!hash_equals((string) $request->route('hash'), sha1($user->email))) {
            return redirect()->route('login')->withErrors(['email' => __('app.email_verification.invalid_link')]);
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('login')->with('status', __('app.email_verification.verified'));
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request): RedirectResponse
    {
        $rules = [
            'email' => 'required|email|exists:users,email',
        ];

        // Add Turnstile validation if enabled
        if (SystemSetting::isTurnstileEnabled()) {
            $rules['cf-turnstile-response'] = ['required', new \App\Rules\TurnstileValid()];
        }

        $request->validate($rules);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Don't reveal if email exists
            return back()->with('status', __('app.email_verification.check_email'));
        }

        // Check if already verified
        if ($user->email_verified_at) {
            return redirect()->route('login')->with('status', __('app.email_verification.already_verified'));
        }

        // Generate verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Send verification email
        try {
            if (SystemSetting::isSmtpEnabled()) {
                \Mail::to($user->email)->send(new VerifyEmailMail($user, $verificationUrl));
            }
        } catch (\Exception $e) {
            \Log::error('Email verification failed: ' . $e->getMessage());
        }

        return back()->with('status', __('app.email_verification.check_email'));
    }
}
