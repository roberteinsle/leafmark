<?php

namespace App\Http\Controllers;

use App\Models\ReadingChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingChallengeController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $currentYear = now()->year;

        $challenge = $user->readingChallenges()->where('year', $currentYear)->first();

        // Get books read this year
        $booksReadThisYear = $user->books()
            ->where('status', 'read')
            ->whereYear('finished_at', $currentYear)
            ->orderBy('finished_at', 'desc')
            ->get();

        // Calculate monthly achievements
        $monthlyStats = [];
        for ($month = 1; $month <= 12; $month++) {
            $count = $user->books()
                ->where('status', 'read')
                ->whereYear('finished_at', $currentYear)
                ->whereMonth('finished_at', $month)
                ->count();

            $monthlyStats[$month] = $count;
        }

        return view('challenge.index', compact('challenge', 'booksReadThisYear', 'monthlyStats', 'currentYear'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'goal' => 'required|integer|min:1|max:1000',
        ]);

        $user = auth()->user();
        $currentYear = now()->year;

        $user->readingChallenges()->create([
            'year' => $currentYear,
            'goal' => $validated['goal'],
        ]);

        return back()->with('success', 'Challenge goal set!');
    }

    public function update(Request $request, ReadingChallenge $challenge): RedirectResponse
    {
        if ($challenge->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'goal' => 'required|integer|min:1|max:1000',
        ]);

        $challenge->update($validated);

        return back()->with('success', 'Challenge goal updated!');
    }

    public function destroy(ReadingChallenge $challenge): RedirectResponse
    {
        if ($challenge->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $challenge->delete();

        return back()->with('success', 'Challenge deleted successfully!');
    }
}
