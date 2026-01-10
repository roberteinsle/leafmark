<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Check if registration is enabled
     */
    public static function isRegistrationEnabled(): bool
    {
        return static::get('registration_enabled', 'true') === 'true';
    }

    /**
     * Get registration mode
     */
    public static function getRegistrationMode(): string
    {
        return static::get('registration_mode', 'open');
    }

    /**
     * Get allowed email domains
     */
    public static function getAllowedEmailDomains(): array
    {
        $domains = static::get('allowed_email_domains', '');
        return $domains ? array_map('trim', explode(',', $domains)) : [];
    }

    /**
     * Get registration code
     */
    public static function getRegistrationCode(): string
    {
        return static::get('registration_code', '');
    }

    /**
     * Check if email domain is allowed
     */
    public static function isEmailDomainAllowed(string $email): bool
    {
        $allowedDomains = static::getAllowedEmailDomains();

        if (empty($allowedDomains)) {
            return true;
        }

        $emailDomain = substr(strrchr($email, "@"), 1);

        return in_array($emailDomain, $allowedDomains);
    }

    /**
     * Check if SMTP is enabled
     */
    public static function isSmtpEnabled(): bool
    {
        return static::get('smtp_enabled', 'false') === 'true';
    }

    /**
     * Get SMTP configuration as array
     */
    public static function getSmtpConfig(): array
    {
        return [
            'host' => static::get('smtp_host', ''),
            'port' => (int) static::get('smtp_port', '587'),
            'encryption' => static::get('smtp_encryption', 'tls'),
            'username' => static::get('smtp_username', ''),
            'password' => static::get('smtp_password', ''),
            'from_address' => static::get('smtp_from_address', ''),
            'from_name' => static::get('smtp_from_name', 'Leafmark'),
        ];
    }

    /**
     * Check if Turnstile is enabled
     */
    public static function isTurnstileEnabled(): bool
    {
        return static::get('turnstile_enabled', 'false') === 'true';
    }

    /**
     * Get Turnstile site key
     */
    public static function getTurnstileSiteKey(): string
    {
        return static::get('turnstile_site_key', '');
    }

    /**
     * Get Turnstile secret key
     */
    public static function getTurnstileSecretKey(): string
    {
        return static::get('turnstile_secret_key', '');
    }
}
