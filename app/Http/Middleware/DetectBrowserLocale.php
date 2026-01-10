<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class DetectBrowserLocale
{
    /**
     * Supported locales
     */
    protected $supportedLocales = ['en', 'de', 'fr', 'it', 'es', 'pl'];

    /**
     * Handle an incoming request.
     * Detects browser language for guest users (login, register pages)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to guest users (not logged in)
        if (!auth()->check()) {
            $locale = $this->detectBrowserLocale($request);

            if ($locale) {
                App::setLocale($locale);
            }
        }

        return $next($request);
    }

    /**
     * Detect browser locale from Accept-Language header
     */
    protected function detectBrowserLocale(Request $request): ?string
    {
        $header = $request->header('Accept-Language');

        if (!$header) {
            return null;
        }

        // Parse Accept-Language header (e.g., "de-DE,de;q=0.9,en;q=0.8")
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
            if (in_array($lang, $this->supportedLocales)) {
                return $lang;
            }
        }

        return null;
    }
}
