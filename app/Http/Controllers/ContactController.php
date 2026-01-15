<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Show the contact form
     */
    public function show(): View
    {
        $turnstileSiteKey = SystemSetting::get('turnstile_site_key', '');
        $turnstileEnabled = SystemSetting::get('turnstile_enabled', 'false') === 'true';

        return view('kontakt', compact('turnstileSiteKey', 'turnstileEnabled'));
    }

    /**
     * Handle contact form submission
     */
    public function submit(Request $request): RedirectResponse
    {
        // Verify Turnstile if enabled
        $turnstileEnabled = SystemSetting::get('turnstile_enabled', 'false') === 'true';

        if ($turnstileEnabled) {
            $validated = $request->validate([
                'cf-turnstile-response' => 'required',
            ], [
                'cf-turnstile-response.required' => __('app.contact.turnstile_required'),
            ]);

            $turnstileSecret = SystemSetting::get('turnstile_secret_key', '');

            if (!$this->verifyTurnstile($request->input('cf-turnstile-response'), $turnstileSecret)) {
                return back()
                    ->withInput()
                    ->withErrors(['turnstile' => __('app.contact.turnstile_failed')]);
            }
        }

        // Validate form data
        $validated = $request->validate([
            'category' => 'required|in:support,feature,bug,privacy',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|min:10|max:5000',
            'privacy' => 'required|accepted',
        ]);

        // Sanitize input
        $category = $validated['category'];
        $name = strip_tags($validated['name']);
        $email = filter_var($validated['email'], FILTER_SANITIZE_EMAIL);
        $message = strip_tags($validated['message']);

        // Get category label
        $categoryLabels = [
            'support' => __('app.contact.category_support'),
            'feature' => __('app.contact.category_feature'),
            'bug' => __('app.contact.category_bug'),
            'privacy' => __('app.contact.category_privacy'),
        ];
        $categoryLabel = $categoryLabels[$category] ?? $category;

        // Send email
        try {
            $contactEmail = SystemSetting::get('contact_email', 'ews@einsle.com');

            Mail::send([], [], function ($message) use ($name, $email, $categoryLabel, $validated, $contactEmail) {
                $message->to($contactEmail)
                    ->from('noreply@leafmark.app', $name)
                    ->replyTo($email, $name)
                    ->subject("Leafmark Kontakt: {$categoryLabel}")
                    ->html($this->buildEmailBody($categoryLabel, $name, $email, $validated['message']));
            });

            return redirect()
                ->route('kontakt')
                ->with('success', __('app.contact.success_message'));

        } catch (\Exception $e) {
            Log::error('Contact form error', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => __('app.contact.error_message')]);
        }
    }

    /**
     * Verify Cloudflare Turnstile response
     */
    private function verifyTurnstile(string $token, string $secret): bool
    {
        if (empty($secret)) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $result['success'] ?? false;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Turnstile verification failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Build email body HTML
     */
    private function buildEmailBody(string $category, string $name, string $email, string $message): string
    {
        $message = nl2br(htmlspecialchars($message));

        return "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
                    <h2 style='color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px;'>
                        Neue Kontaktanfrage
                    </h2>

                    <div style='background-color: white; padding: 20px; border-radius: 8px; margin-top: 20px;'>
                        <p><strong>Kategorie:</strong> {$category}</p>
                        <p><strong>Name:</strong> {$name}</p>
                        <p><strong>E-Mail:</strong> <a href='mailto:{$email}'>{$email}</a></p>

                        <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>

                        <p><strong>Nachricht:</strong></p>
                        <div style='background-color: #f3f4f6; padding: 15px; border-radius: 6px;'>
                            {$message}
                        </div>
                    </div>

                    <p style='margin-top: 20px; font-size: 12px; color: #6b7280;'>
                        Diese Nachricht wurde über das Kontaktformular auf leafmark.app gesendet.
                    </p>
                </div>
            </body>
            </html>
        ";
    }
}
