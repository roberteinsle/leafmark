<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\EmailLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            // Don't reveal if email exists or not for security
            return back()->with('status', __('app.password_reset.link_sent'));
        }

        // Generate token
        $token = Str::random(64);

        // Store in database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send email
        try {
            if (SystemSetting::isSmtpEnabled()) {
                \Mail::to($user->email)->send(new PasswordResetMail($token, $user->email));

                EmailLog::logSuccess(
                    $user->email,
                    __('app.password_reset.email_subject'),
                    'password_reset',
                    null,
                    SystemSetting::getSmtpConfig()
                );
            }
        } catch (\Exception $e) {
            \Log::error('Password reset email failed: ' . $e->getMessage());

            EmailLog::logFailure(
                $user->email,
                __('app.password_reset.email_subject'),
                $e->getMessage(),
                'password_reset',
                null,
                SystemSetting::getSmtpConfig(),
                $e->getTraceAsString()
            );
        }

        return back()->with('status', __('app.password_reset.link_sent'));
    }
}
