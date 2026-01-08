<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserSettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'user' => auth()->user()
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'preferred_language' => 'required|in:en,de',
        ]);

        auth()->user()->update($validated);

        return redirect()->route('settings.edit')
            ->with('success', 'Settings updated successfully!');
    }
}
