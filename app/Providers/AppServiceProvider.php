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
        // Register a helper function for locale-aware routes
        if (!function_exists('localeRoute')) {
            /**
             * Generate a locale-aware route URL
             *
             * @param string $name Route name without locale suffix
             * @param array $parameters Route parameters
             * @param string|null $locale Override locale (defaults to current)
             * @return string
             */
            function localeRoute(string $name, array $parameters = [], ?string $locale = null): string
            {
                $locale = $locale ?? app()->getLocale();
                return route($name . '.' . $locale, $parameters);
            }
        }

        // Register Blade directive for locale routes
        Blade::directive('localeRoute', function ($expression) {
            return "<?php echo localeRoute($expression); ?>";
        });
    }
}
