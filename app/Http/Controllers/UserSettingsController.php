<?php

namespace App\Http\Controllers;

use App\Services\LanguageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserSettingsController extends Controller
{
    public function __construct(
        private LanguageService $languageService
    ) {}

    public function edit(): View
    {
        return view('settings.edit', [
            'user' => auth()->user(),
            'availableLanguages' => $this->languageService->getLanguageNames(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $availableLanguages = $this->languageService->getAvailableLanguages();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'preferred_language' => 'required|in:' . implode(',', $availableLanguages),
            'google_books_api_key' => 'nullable|string|max:255',
        ]);

        auth()->user()->update($validated);

        return redirect()->route('settings.edit')
            ->with('success', 'Settings updated successfully!');
    }
}
