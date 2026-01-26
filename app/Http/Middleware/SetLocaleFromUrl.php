<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUrl
{
    /**
     * Supported locales
     */
    protected $supportedLocales = ['en', 'de', 'fr', 'it', 'es', 'pl'];

    /**
     * Handle an incoming request.
     * Sets locale based on URL prefix (e.g., /en/, /de/, /fr/)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $locale = null): Response
    {
        // If locale is provided as middleware parameter (from route group), use it
        if ($locale && in_array($locale, $this->supportedLocales)) {
            App::setLocale($locale);

            // Store locale in request for easy access in views and controllers
            $request->attributes->set('locale', $locale);
        }

        return $next($request);
    }

    /**
     * Detect the preferred locale for root URL redirect
     * Priority: User preference > Browser language > Default (en)
     */
    public static function detectPreferredLocale(Request $request): string
    {
        $supportedLocales = ['en', 'de', 'fr', 'it', 'es', 'pl'];

        // 1. Check if user is authenticated and has a preferred language
        if (auth()->check() && auth()->user()->preferred_language) {
            $userLocale = auth()->user()->preferred_language;
            if (in_array($userLocale, $supportedLocales)) {
                return $userLocale;
            }
        }

        // 2. Detect from browser Accept-Language header
        $header = $request->header('Accept-Language');
        if ($header) {
            $languages = [];
            preg_match_all('/([a-z]{2})(?:-[A-Z]{2})?(?:;q=([0-9.]+))?/i', $header, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $lang = strtolower($match[1]);
                $quality = isset($match[2]) ? (float) $match[2] : 1.0;
                $languages[$lang] = $quality;
            }

            // Sort by quality (preference)
            arsort($languages);

            // Find first supported language
            foreach (array_keys($languages) as $lang) {
                if (in_array($lang, $supportedLocales)) {
                    return $lang;
                }
            }
        }

        // 3. Default to English
        return 'en';
    }
}
