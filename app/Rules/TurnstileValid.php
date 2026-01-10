<?php

namespace App\Rules;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileValid implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Skip validation if Turnstile is not enabled
        if (!SystemSetting::isTurnstileEnabled()) {
            return;
        }

        $secretKey = SystemSetting::getTurnstileSecretKey();

        if (empty($secretKey)) {
            Log::warning('Turnstile enabled but secret key not configured');
            return; // Don't fail if not configured
        }

        if (empty($value)) {
            $fail('The Turnstile verification is required.');
            return;
        }

        try {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secretKey,
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            $result = $response->json();

            if (!isset($result['success']) || !$result['success']) {
                $fail('The Turnstile verification failed. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Turnstile validation error: ' . $e->getMessage());
            $fail('The Turnstile verification failed. Please try again.');
        }
    }
}
