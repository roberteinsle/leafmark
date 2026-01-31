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
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Handle password change
        if ($request->filled('password')) {
            // Verify current password
            if (!$request->filled('current_password')) {
                return back()->withErrors(['current_password' => 'Current password is required to set a new password.']);
            }

            if (!\Hash::check($request->current_password, auth()->user()->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }

            // Update password
            $validated['password'] = \Hash::make($request->password);
        } else {
            // Remove password fields if not changing password
            unset($validated['password'], $validated['current_password']);
        }

        // Remove current_password from validated data (we don't want to save it)
        unset($validated['current_password']);

        auth()->user()->update($validated);

        return redirect()->route('settings.edit')
            ->with('success', 'Settings updated successfully!');
    }
}
