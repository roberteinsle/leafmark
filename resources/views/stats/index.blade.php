@extends('layouts.app')

@section('title', __('app.statistics.title'))

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header + Year Selector -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.statistics.title') }}</h1>

        <form method="GET" action="{{ route('stats.index') }}" class="flex items-center gap-2">
            <label for="year-select" class="text-sm font-medium text-gray-700">{{ __('app.statistics.select_year') }}:</label>
            <select name="year" id="year-select" onchange="this.form.submit()"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                        {{ $year }}
                        @if($year == $currentYear) ({{ __('app.statistics.current_year') }}) @endif
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($basicStats['total_books_read'] === 0 && $basicStats['currently_reading'] === 0)
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('app.statistics.no_stats_yet') }}</h3>
            <p class="mt-2 text-sm text-gray-500">{{ __('app.statistics.no_stats_description') }}</p>
            <a href="{{ route('books.index') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                {{ __('app.statistics.start_reading') }}
            </a>
        </div>
    @else
        <!-- Basic Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ $basicStats['total_books_read'] }}</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('app.statistics.total_books_read') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ number_format($basicStats['total_pages_read']) }}</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('app.statistics.total_pages_read') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                <div class="text-3xl font-bold text-indigo-600">
                    @if($basicStats['avg_rating'])
                        {{ $basicStats['avg_rating'] }} <span class="text-yellow-400 text-xl">&#9733;</span>
                    @else
                        &mdash;
                    @endif
                </div>
                <div class="text-sm text-gray-600 mt-1">{{ __('app.statistics.avg_rating') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ $basicStats['books_read_this_year'] }}</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('app.statistics.books_this_year') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ number_format($basicStats['pages_read_this_year']) }}</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('app.statistics.pages_this_year') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ $basicStats['currently_reading'] }}</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('app.statistics.currently_reading') }}</div>
            </div>
        </div>

        @if($challenge)
        <!-- Challenge Progress -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('app.statistics.challenge_progress') }} {{ $selectedYear }}</h2>
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>{{ $challenge->progress }} {{ __('app.challenge.of') }} {{ $challenge->goal }} {{ __('app.challenge.books_read') }}</span>
                <span class="font-bold text-indigo-600">{{ $challenge->progress_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-indigo-600 h-3 rounded-full transition-all duration-300" style="width: {{ $challenge->progress_percentage }}%"></div>
            </div>
        </div>
        @endif

        <!-- Books Per Month Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.statistics.books_per_month') }}</h2>
            @if(array_sum($timeStats['books_per_month']) > 0 || array_sum($timeStats['books_per_month_last_year']) > 0)
                <div style="height: 300px;">
                    <canvas id="booksPerMonthChart"></canvas>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">{{ __('app.statistics.no_finished_books') }}</p>
            @endif
        </div>

        <!-- Time-based Stats Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Reading Speed -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.statistics.reading_speed') }}</h2>
                @if($timeStats['avg_days_per_book'])
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-gray-600">{{ __('app.statistics.avg_days_per_book') }}</span>
                            <span class="text-xl font-bold text-indigo-600">{{ $timeStats['avg_days_per_book'] }} {{ __('app.statistics.days') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-gray-600">{{ __('app.statistics.avg_pages_per_day') }}</span>
                            <span class="text-xl font-bold text-indigo-600">{{ $timeStats['avg_pages_per_day'] }}</span>
                        </div>
                        @if($timeStats['longest_book'])
                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <div>
                                <span class="text-gray-600">{{ __('app.statistics.longest_read') }}</span>
                                <div class="text-xs text-gray-400">{{ $timeStats['longest_book']['title'] }}</div>
                            </div>
                            <span class="font-semibold text-gray-900">{{ $timeStats['longest_book']['days'] }} {{ __('app.statistics.days') }}</span>
                        </div>
                        @endif
                        @if($timeStats['shortest_book'])
                        <div class="flex justify-between items-center py-3">
                            <div>
                                <span class="text-gray-600">{{ __('app.statistics.shortest_read') }}</span>
                                <div class="text-xs text-gray-400">{{ $timeStats['shortest_book']['title'] }}</div>
                            </div>
                            <span class="font-semibold text-gray-900">{{ $timeStats['shortest_book']['days'] }} {{ __('app.statistics.days') }}</span>
                        </div>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">{{ __('app.statistics.no_finished_books') }}</p>
                @endif
            </div>

            <!-- Year Comparison -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.statistics.year_comparison') }}</h2>
                <div class="space-y-4">
                    <!-- Books comparison -->
                    <div class="py-3 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">{{ __('app.statistics.total_books_read') }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-center flex-1">
                                <div class="text-2xl font-bold text-indigo-600">{{ $basicStats['books_read_this_year'] }}</div>
                                <div class="text-xs text-gray-500">{{ $selectedYear }}</div>
                            </div>
                            <span class="text-gray-400">vs</span>
                            <div class="text-center flex-1">
                                <div class="text-2xl font-bold text-gray-400">{{ $timeStats['books_last_year'] }}</div>
                                <div class="text-xs text-gray-500">{{ $timeStats['previous_year'] }}</div>
                            </div>
                            @php
                                $bookDiff = $basicStats['books_read_this_year'] - $timeStats['books_last_year'];
                            @endphp
                            <div class="text-center">
                                @if($bookDiff > 0)
                                    <span class="text-green-600 font-semibold">+{{ $bookDiff }}</span>
                                @elseif($bookDiff < 0)
                                    <span class="text-red-600 font-semibold">{{ $bookDiff }}</span>
                                @else
                                    <span class="text-gray-500 font-semibold">=</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Pages comparison -->
                    <div class="py-3 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">{{ __('app.statistics.total_pages_read') }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-center flex-1">
                                <div class="text-2xl font-bold text-indigo-600">{{ number_format($basicStats['pages_read_this_year']) }}</div>
                                <div class="text-xs text-gray-500">{{ $selectedYear }}</div>
                            </div>
                            <span class="text-gray-400">vs</span>
                            <div class="text-center flex-1">
                                <div class="text-2xl font-bold text-gray-400">{{ number_format($timeStats['pages_last_year']) }}</div>
                                <div class="text-xs text-gray-500">{{ $timeStats['previous_year'] }}</div>
                            </div>
                            @php
                                $pageDiff = $basicStats['pages_read_this_year'] - $timeStats['pages_last_year'];
                            @endphp
                            <div class="text-center">
                                @if($pageDiff > 0)
                                    <span class="text-green-600 font-semibold">+{{ number_format($pageDiff) }}</span>
                                @elseif($pageDiff < 0)
                                    <span class="text-red-600 font-semibold">{{ number_format($pageDiff) }}</span>
                                @else
                                    <span class="text-gray-500 font-semibold">=</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Best/Worst months -->
                    @if($timeStats['best_month'])
                    <div class="py-3">
                        @php
                            $monthNames = [
                                1 => __('app.statistics.january'), 2 => __('app.statistics.february'),
                                3 => __('app.statistics.march'), 4 => __('app.statistics.april'),
                                5 => __('app.statistics.may'), 6 => __('app.statistics.june'),
                                7 => __('app.statistics.july'), 8 => __('app.statistics.august'),
                                9 => __('app.statistics.september'), 10 => __('app.statistics.october'),
                                11 => __('app.statistics.november'), 12 => __('app.statistics.december'),
                            ];
                        @endphp
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <span class="text-gray-600">{{ __('app.statistics.best_month') }}:</span>
                                <span class="font-semibold text-green-600">{{ $monthNames[$timeStats['best_month']['month']] }}</span>
                                <span class="text-gray-500">({{ __('app.statistics.books_in_month', ['count' => $timeStats['best_month']['count']]) }})</span>
                            </div>
                        </div>
                        @if($timeStats['worst_month'] && $timeStats['worst_month']['month'] !== $timeStats['best_month']['month'])
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-gray-600">{{ __('app.statistics.worst_month') }}:</span>
                                <span class="font-semibold text-orange-600">{{ $monthNames[$timeStats['worst_month']['month']] }}</span>
                                <span class="text-gray-500">({{ __('app.statistics.books_in_month', ['count' => $timeStats['worst_month']['count']]) }})</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content Analysis Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Language Distribution -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.statistics.language_distribution') }}</h2>
                @if($contentStats['languages']->isNotEmpty())
                    <div style="height: 250px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="languageChart"></canvas>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">{{ __('app.statistics.no_data') }}</p>
                @endif
            </div>

            <!-- Format Distribution -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.statistics.format_distribution') }}</h2>
                @if($contentStats['formats']->isNotEmpty())
                    <div style="height: 250px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="formatChart"></canvas>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">{{ __('app.statistics.no_data') }}</p>
                @endif
            </div>

            <!-- Top Authors -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.statistics.top_authors') }}</h2>
                @if($contentStats['top_authors']->isNotEmpty())
                    <div style="height: 250px;">
                        <canvas id="authorsChart"></canvas>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">{{ __('app.statistics.no_data') }}</p>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@php
    $monthLabelsArr = [
        __('app.statistics.january'), __('app.statistics.february'),
        __('app.statistics.march'), __('app.statistics.april'),
        __('app.statistics.may'), __('app.statistics.june'),
        __('app.statistics.july'), __('app.statistics.august'),
        __('app.statistics.september'), __('app.statistics.october'),
        __('app.statistics.november'), __('app.statistics.december'),
    ];
    $langLabelsArr = $contentStats['languages']->keys()->map(fn($code) => strtoupper($code))->values()->toArray();
    $langValuesArr = $contentStats['languages']->values()->toArray();
    $formatLabelsForChart = [
        'digital' => __('app.statistics.format_digital'),
        'paperback' => __('app.statistics.format_paperback'),
        'hardcover' => __('app.statistics.format_hardcover'),
        'audiobook' => __('app.statistics.format_audiobook'),
        'magazine' => __('app.statistics.format_magazine'),
        'spiral_bound' => __('app.statistics.format_spiral_bound'),
        'leather_bound' => __('app.statistics.format_leather_bound'),
        'journal' => __('app.statistics.format_journal'),
        'comic' => __('app.statistics.format_comic'),
        'graphic_novel' => __('app.statistics.format_graphic_novel'),
        'manga' => __('app.statistics.format_manga'),
        'box_set' => __('app.statistics.format_box_set'),
        'omnibus' => __('app.statistics.format_omnibus'),
        'reference' => __('app.statistics.format_reference'),
        'other' => __('app.statistics.format_other'),
    ];
    $translatedFormatLabelsArr = $contentStats['formats']->keys()->map(fn($key) => $formatLabelsForChart[$key] ?? $key)->values()->toArray();
    $formatValuesArr = $contentStats['formats']->values()->toArray();
    $authorLabelsArr = $contentStats['top_authors']->keys()->toArray();
    $authorValuesArr = $contentStats['top_authors']->values()->toArray();
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthLabels = @json($monthLabelsArr);

        const chartColors = [
            '#6366f1', '#8b5cf6', '#a78bfa', '#c4b5fd',
            '#818cf8', '#34d399', '#fbbf24', '#fb923c',
            '#f472b6', '#94a3b8', '#60a5fa', '#4ade80'
        ];

        // Books Per Month Bar Chart
        const booksPerMonthEl = document.getElementById('booksPerMonthChart');
        if (booksPerMonthEl) {
            new Chart(booksPerMonthEl, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [
                        {
                            label: '{{ $selectedYear }}',
                            data: @json(array_values($timeStats['books_per_month'])),
                            backgroundColor: 'rgba(99, 102, 241, 0.8)',
                            borderRadius: 6,
                        },
                        {
                            label: '{{ $timeStats["previous_year"] }}',
                            data: @json(array_values($timeStats['books_per_month_last_year'])),
                            backgroundColor: 'rgba(199, 210, 254, 0.8)',
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0 }
                        }
                    }
                }
            });
        }

        // Language Distribution Doughnut
        const langEl = document.getElementById('languageChart');
        if (langEl) {
            const langLabels = @json($langLabelsArr);
            new Chart(langEl, {
                type: 'doughnut',
                data: {
                    labels: langLabels,
                    datasets: [{
                        data: @json($langValuesArr),
                        backgroundColor: chartColors.slice(0, langLabels.length),
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 12, boxWidth: 12 } }
                    }
                }
            });
        }

        // Format Distribution Doughnut
        const formatEl = document.getElementById('formatChart');
        if (formatEl) {
            new Chart(formatEl, {
                type: 'doughnut',
                data: {
                    labels: @json($translatedFormatLabelsArr),
                    datasets: [{
                        data: @json($formatValuesArr),
                        backgroundColor: chartColors.slice(0, {{ count($formatValuesArr) }}),
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 12, boxWidth: 12 } }
                    }
                }
            });
        }

        // Top Authors Horizontal Bar
        const authorsEl = document.getElementById('authorsChart');
        if (authorsEl) {
            new Chart(authorsEl, {
                type: 'bar',
                data: {
                    labels: @json($authorLabelsArr),
                    datasets: [{
                        data: @json($authorValuesArr),
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                    }
                }
            });
        }
    });
</script>
@endpush
