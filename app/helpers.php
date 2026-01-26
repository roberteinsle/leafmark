<?php

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
