<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        // Gilt für alle Anmeldewege: Passkey, Magic Link, Passwort+2FA
        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof \App\Models\User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });
    }

    /**
     * Standard Abschnitt 11/12.1: Jede Route, die Anmeldung auslöst, teuer ist
     * oder externe APIs aufruft, hat ein Limit. Überschreitung → HTTP 429.
     */
    private function configureRateLimiting(): void
    {
        $emailAndIp = fn (Request $request) => Str::lower((string) $request->input('email')).'|'.$request->ip();

        // Login per Passwort und Magic-Link-Anforderung: 5 pro Minute je Adresse+IP, 20 pro Stunde je IP
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by($emailAndIp($request)),
            Limit::perHour(20)->by('ip:'.$request->ip()),
        ]);

        RateLimiter::for('register', fn (Request $request) => Limit::perHour(10)->by($request->ip()));

        // Externe Buch-APIs (Google Books, Open Library, BookBrainz, Big Book, Amazon):
        // begrenzt Kosten und schützt die API-Kontingente der Instanz.
        RateLimiter::for('external-api', fn (Request $request) => [
            Limit::perMinute(30)->by('user:'.$request->user()?->id),
            Limit::perDay(500)->by('user-day:'.$request->user()?->id),
        ]);

        // Teure Endpunkte: Exporte, Imports
        RateLimiter::for('expensive', fn (Request $request) => [
            Limit::perMinute(5)->by('user:'.$request->user()?->id),
            Limit::perDay(50)->by('user-day:'.$request->user()?->id),
        ]);
    }
}
