<?php

namespace App\Http\Controllers;

use App\Models\ReadingChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingChallengeController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $currentYear = now()->year;

        // Get selected year from request or default to current year
        $selectedYear = $request->get('year', $currentYear);

        // Get all available years (years where user has challenges or finished books)
        $availableYears = $user->readingChallenges()
            ->select('year')
            ->distinct()
            ->pluck('year')
            ->merge(
                $user->books()
                    ->where('status', 'read')
                    ->whereNotNull('finished_at')
                    ->get()
                    ->map(fn($book) => $book->finished_at->year)
                    ->unique()
            )
            ->unique()
            ->sort()
            ->values();

        // Ensure current year is always available
        if (!$availableYears->contains($currentYear)) {
            $availableYears->push($currentYear);
            $availableYears = $availableYears->sort()->values();
        }

        $challenge = $user->readingChallenges()->where('year', $selectedYear)->first();

        // Get books read this year
        $booksReadThisYear = $user->books()
            ->where('status', 'read')
            ->whereYear('finished_at', $selectedYear)
            ->orderBy('finished_at', 'desc')
            ->get();

        // Calculate monthly achievements
        $monthlyStats = [];
        for ($month = 1; $month <= 12; $month++) {
            $count = $user->books()
                ->where('status', 'read')
                ->whereYear('finished_at', $selectedYear)
                ->whereMonth('finished_at', $month)
                ->count();

            $monthlyStats[$month] = $count;
        }

        return view('challenge.index', compact('challenge', 'booksReadThisYear', 'monthlyStats', 'currentYear', 'selectedYear', 'availableYears'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'goal' => 'required|integer|min:1|max:1000',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $user = auth()->user();

        // Check if challenge already exists for this year
        $existingChallenge = $user->readingChallenges()->where('year', $validated['year'])->first();
        if ($existingChallenge) {
            return back()->withErrors(['year' => 'A challenge for this year already exists.']);
        }

        $user->readingChallenges()->create([
            'year' => $validated['year'],
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
