<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        // Set locale based on authenticated user's preference or default from .env
        if (auth()->check() && auth()->user()->preferred_language) {
            app()->setLocale(auth()->user()->preferred_language);
        } else {
            app()->setLocale(config('app.locale', 'en'));
        }
    }
}
