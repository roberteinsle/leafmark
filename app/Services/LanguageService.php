<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class LanguageService
{
    /**
     * Get all available languages by scanning the lang directory
     *
     * @return array Array of language codes (e.g., ['en', 'de', 'es', 'fr', 'it', 'pl'])
     */
    public function getAvailableLanguages(): array
    {
        $langPath = base_path('lang');

        if (!File::exists($langPath)) {
            return ['en']; // Default to English if lang directory doesn't exist
        }

        $directories = File::directories($langPath);
        $languages = [];

        foreach ($directories as $directory) {
            $languageCode = basename($directory);

            // Only include if it has an app.php translation file
            if (File::exists($directory . '/app.php')) {
                $languages[] = $languageCode;
            }
        }

        // Ensure English is always available as fallback
        if (!in_array('en', $languages)) {
            $languages[] = 'en';
        }

        sort($languages);

        return $languages;
    }

    /**
     * Get language names for all available languages
     *
     * @return array Associative array of language codes to display names
     */
    public function getLanguageNames(): array
    {
        $availableLanguages = $this->getAvailableLanguages();
        $languageNames = [];

        foreach ($availableLanguages as $code) {
            // Get the language name from the language file itself
            $name = __('app.languages.' . $code, [], $code);

            // If translation doesn't exist, use a default mapping
            if ($name === 'app.languages.' . $code) {
                $name = $this->getDefaultLanguageName($code);
            }

            $languageNames[$code] = $name;
        }

        return $languageNames;
    }

    /**
     * Get default language name mapping
     *
     * @param string $code
     * @return string
     */
    private function getDefaultLanguageName(string $code): string
    {
        $defaultNames = [
            'en' => 'English',
            'de' => 'Deutsch',
            'es' => 'Español',
            'fr' => 'Français',
            'it' => 'Italiano',
            'pl' => 'Polski',
        ];

        return $defaultNames[$code] ?? strtoupper($code);
    }

    /**
     * Validate if a language code is available
     *
     * @param string $code
     * @return bool
     */
    public function isLanguageAvailable(string $code): bool
    {
        return in_array($code, $this->getAvailableLanguages());
    }
}
