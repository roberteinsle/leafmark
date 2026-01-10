<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class DynamicMailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only configure SMTP if enabled and we have a database connection
        try {
            if (SystemSetting::isSmtpEnabled()) {
                $smtp = SystemSetting::getSmtpConfig();

                Config::set('mail.default', 'smtp');
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $smtp['host'],
                    'port' => $smtp['port'],
                    'encryption' => $smtp['encryption'],
                    'username' => $smtp['username'],
                    'password' => $smtp['password'],
                    'timeout' => null,
                ]);
                Config::set('mail.from.address', $smtp['from_address']);
                Config::set('mail.from.name', $smtp['from_name']);
            }
        } catch (\Exception $e) {
            // Silently fail if database is not ready (during migrations, etc.)
            \Log::debug('Mail config not loaded: ' . $e->getMessage());
        }
    }
}
