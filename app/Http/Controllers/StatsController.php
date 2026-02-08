<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatsController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $currentYear = now()->year;
        $selectedYear = (int) $request->get('year', $currentYear);

        $availableYears = $this->getAvailableYears($user, $currentYear);
        $basicStats = $this->getBasicStats($user, $selectedYear);
        $challenge = $user->readingChallenges()->where('year', $selectedYear)->first();
        $timeStats = $this->getTimeBasedStats($user, $selectedYear);
        $contentStats = $this->getContentStats($user);

        return view('stats.index', compact(
            'currentYear',
            'selectedYear',
            'availableYears',
            'basicStats',
            'challenge',
            'timeStats',
            'contentStats'
        ));
    }

    private function getAvailableYears($user, int $currentYear): \Illuminate\Support\Collection
    {
        return $user->books()
            ->where('status', 'read')
            ->whereNotNull('finished_at')
            ->selectRaw('DISTINCT CAST(strftime("%Y", finished_at) AS INTEGER) as year')
            ->pluck('year')
            ->filter()
            ->push($currentYear)
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function getBasicStats($user, int $selectedYear): array
    {
        $totalBooksRead = $user->books()->read()->count();

        $totalPagesRead = (int) $user->books()->read()
            ->whereNotNull('page_count')
            ->sum('page_count');

        $avgRating = $user->books()->read()
            ->whereNotNull('rating')
            ->where('rating', '>', 0)
            ->avg('rating');

        $booksReadThisYear = $user->books()->read()
            ->whereRaw('CAST(strftime("%Y", finished_at) AS INTEGER) = ?', [$selectedYear])
            ->count();

        $pagesReadThisYear = (int) $user->books()->read()
            ->whereRaw('CAST(strftime("%Y", finished_at) AS INTEGER) = ?', [$selectedYear])
            ->whereNotNull('page_count')
            ->sum('page_count');

        $currentlyReading = $user->books()->currentlyReading()->count();

        return [
            'total_books_read' => $totalBooksRead,
            'total_pages_read' => $totalPagesRead,
            'avg_rating' => $avgRating ? round((float) $avgRating, 1) : null,
            'books_read_this_year' => $booksReadThisYear,
            'pages_read_this_year' => $pagesReadThisYear,
            'currently_reading' => $currentlyReading,
        ];
    }

    private function getTimeBasedStats($user, int $selectedYear): array
    {
        // Books per month for selected year
        $booksPerMonth = [];
        $monthlyData = $user->books()->read()
            ->whereRaw('CAST(strftime("%Y", finished_at) AS INTEGER) = ?', [$selectedYear])
            ->selectRaw('CAST(strftime("%m", finished_at) AS INTEGER) as month, COUNT(*) as count')
            ->groupByRaw('strftime("%m", finished_at)')
            ->pluck('count', 'month');

        for ($m = 1; $m <= 12; $m++) {
            $booksPerMonth[$m] = $monthlyData->get($m, 0);
        }

        // Previous year for comparison
        $previousYear = $selectedYear - 1;
        $booksPerMonthLastYear = [];
        $monthlyDataLastYear = $user->books()->read()
            ->whereRaw('CAST(strftime("%Y", finished_at) AS INTEGER) = ?', [$previousYear])
            ->selectRaw('CAST(strftime("%m", finished_at) AS INTEGER) as month, COUNT(*) as count')
            ->groupByRaw('strftime("%m", finished_at)')
            ->pluck('count', 'month');

        for ($m = 1; $m <= 12; $m++) {
            $booksPerMonthLastYear[$m] = $monthlyDataLastYear->get($m, 0);
        }

        // Best/worst months
        $bestMonth = null;
        $worstMonth = null;
        $nonZeroMonths = collect($booksPerMonth)->filter(fn($count) => $count > 0);

        if ($nonZeroMonths->isNotEmpty()) {
            $bestMonthNum = $nonZeroMonths->sortDesc()->keys()->first();
            $worstMonthNum = $nonZeroMonths->sort()->keys()->first();
            $bestMonth = ['month' => $bestMonthNum, 'count' => $nonZeroMonths[$bestMonthNum]];
            $worstMonth = ['month' => $worstMonthNum, 'count' => $nonZeroMonths[$worstMonthNum]];
        }

        // Reading speed - load books with both dates
        $readBooksWithDates = $user->books()->read()
            ->whereRaw('CAST(strftime("%Y", finished_at) AS INTEGER) = ?', [$selectedYear])
            ->whereNotNull('started_at')
            ->whereNotNull('finished_at')
            ->whereNotNull('page_count')
            ->where('page_count', '>', 0)
            ->select('title', 'started_at', 'finished_at', 'page_count')
            ->get();

        $avgDaysPerBook = null;
        $avgPagesPerDay = null;
        $longestBook = null;
        $shortestBook = null;

        if ($readBooksWithDates->isNotEmpty()) {
            $booksWithDuration = $readBooksWithDates->map(function ($book) {
                $days = Carbon::parse($book->started_at)->diffInDays(Carbon::parse($book->finished_at));
                $days = max($days, 1);
                return [
                    'title' => $book->title,
                    'page_count' => $book->page_count,
                    'days' => $days,
                    'pages_per_day' => round($book->page_count / $days, 1),
                ];
            });

            $avgDaysPerBook = round($booksWithDuration->avg('days'), 1);
            $avgPagesPerDay = round($booksWithDuration->avg('pages_per_day'), 1);
            $longestBook = $booksWithDuration->sortByDesc('days')->first();
            $shortestBook = $booksWithDuration->sortBy('days')->first();
        }

        // Year comparison
        $booksLastYear = $user->books()->read()
            ->whereRaw('CAST(strftime("%Y", finished_at) AS INTEGER) = ?', [$previousYear])
            ->count();

        $pagesLastYear = (int) $user->books()->read()
            ->whereRaw('CAST(strftime("%Y", finished_at) AS INTEGER) = ?', [$previousYear])
            ->whereNotNull('page_count')
            ->sum('page_count');

        return [
            'books_per_month' => $booksPerMonth,
            'books_per_month_last_year' => $booksPerMonthLastYear,
            'best_month' => $bestMonth,
            'worst_month' => $worstMonth,
            'avg_days_per_book' => $avgDaysPerBook,
            'avg_pages_per_day' => $avgPagesPerDay,
            'longest_book' => $longestBook,
            'shortest_book' => $shortestBook,
            'books_last_year' => $booksLastYear,
            'pages_last_year' => $pagesLastYear,
            'previous_year' => $previousYear,
        ];
    }

    private function getContentStats($user): array
    {
        $languages = $user->books()->read()
            ->whereNotNull('language')
            ->where('language', '!=', '')
            ->selectRaw('language, COUNT(*) as count')
            ->groupBy('language')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'language');

        $formats = $user->books()->read()
            ->whereNotNull('format')
            ->selectRaw('format, COUNT(*) as count')
            ->groupBy('format')
            ->orderByDesc('count')
            ->pluck('count', 'format');

        $topAuthors = $user->books()->read()
            ->whereNotNull('author')
            ->where('author', '!=', '')
            ->selectRaw('author, COUNT(*) as count')
            ->groupBy('author')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'author');

        return [
            'languages' => $languages,
            'formats' => $formats,
            'top_authors' => $topAuthors,
        ];
    }
}
