<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\Invitation;
use App\Models\EmailLog;
use App\Mail\VerifyEmailMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
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

        $invitationToken = $request->get('token');
        $invitation = null;

        if ($invitationToken) {
            $invitation = Invitation::where('token', $invitationToken)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->first();
        }

        return view('auth.register', compact('invitation', 'invitationToken'));
    }

    public function register(Request $request): RedirectResponse
    {
        // Check if registration is enabled
        if (!SystemSetting::isRegistrationEnabled()) {
            abort(403, 'Registration is currently disabled.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // Add Turnstile validation if enabled
        if (SystemSetting::isTurnstileEnabled()) {
            $rules['cf-turnstile-response'] = ['required', new \App\Rules\TurnstileValid()];
        }

        $request->validate($rules);

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

        if ($registrationMode === 'invitation') {
            // Check invitation token
            $request->validate([
                'invitation_token' => ['required', 'string'],
            ]);

            $invitation = Invitation::findValidByTokenAndEmail(
                $request->invitation_token,
                $request->email
            );

            if (!$invitation) {
                return back()->withErrors([
                    'email' => 'Invalid or expired invitation.',
                ])->withInput();
            }
        }

        // Check if this is the first user (will become admin)
        $isFirstUser = User::count() === 0;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => null, // Set to null, requires email verification
            'is_admin' => $isFirstUser, // First user becomes admin automatically
        ]);

        // Mark invitation as used if applicable
        if ($registrationMode === 'invitation' && isset($invitation)) {
            $invitation->markAsUsed();
        }

        event(new Registered($user));

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

                EmailLog::logSuccess(
                    $user->email,
                    __('app.email_verification.email_subject'),
                    'email_verification',
                    $user->id,
                    SystemSetting::getSmtpConfig()
                );
            }
        } catch (\Exception $e) {
            \Log::error('Email verification failed: ' . $e->getMessage());

            EmailLog::logFailure(
                $user->email,
                __('app.email_verification.email_subject'),
                $e->getMessage(),
                'email_verification',
                $user->id,
                SystemSetting::getSmtpConfig(),
                $e->getTraceAsString()
            );
        }

        // Do NOT log in the user - they must verify email first
        return redirect()->route('verify.notice')->with('status', __('app.email_verification.check_email'));
    }
}
