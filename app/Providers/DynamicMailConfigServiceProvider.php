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
                    // Laravel 13: 'scheme' statt 'encryption'. smtps = SSL/TLS ab Verbindungsbeginn (Port 465),
                    // smtp = Klartext mit automatischem STARTTLS, wenn der Server es anbietet (Port 587)
                    'scheme' => $smtp['encryption'] === 'ssl' ? 'smtps' : 'smtp',
                    'host' => $smtp['host'],
                    'port' => $smtp['port'],
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
