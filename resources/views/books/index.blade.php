@extends('layouts.app')

@section('title', __('app.books.title'))

@section('content')
<div class="px-4">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.books.title') }}</h1>
        <a href="{{ route('books.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg">
            {{ __('app.books.add_book') }}
        </a>
    </div>

    <!-- Reading Challenge Widget -->
    @if($challenge)
    <div class="mb-6 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold">{{ __('app.challenge.title') }} {{ now()->year }}</h3>
                        <p class="text-sm opacity-90">{{ __('app.challenge.your_goal') }}: {{ $challenge->goal }} {{ __('app.challenge.books_goal') }}</p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-2">
                    <div class="flex justify-between text-sm mb-1">
                        <span>{{ $challenge->progress }} {{ __('app.challenge.of') }} {{ $challenge->goal }}</span>
                        <span class="font-bold">{{ $challenge->progress_percentage }}%</span>
                    </div>
                    <div class="w-full bg-white bg-opacity-30 rounded-full h-3">
                        <div class="bg-white h-3 rounded-full transition-all duration-300 shadow-sm"
                             style="width: {{ $challenge->progress_percentage }}%"></div>
                    </div>
                </div>

                @if($challenge->is_completed)
                    <p class="text-sm font-semibold mt-2">🎉 {{ __('app.challenge.goal_completed') }}</p>
                @elseif($challenge->progress > 0)
                    @php
                        $remaining = $challenge->goal - $challenge->progress;
                        $daysLeft = now()->diffInDays(now()->endOfYear());
                        $booksPerMonth = $daysLeft > 0 ? round(($remaining / $daysLeft) * 30, 1) : 0;
                    @endphp
                    <p class="text-sm opacity-90 mt-2">
                        {{ $remaining }} {{ __('app.challenge.books_remaining') }}
                        @if($booksPerMonth > 0)
                            • ~{{ $booksPerMonth }} {{ __('app.challenge.books_per_month') }}
                        @endif
                    </p>
                @endif
            </div>

            <div class="ml-6">
                <a href="{{ route('challenge.index') }}" class="inline-block bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-medium py-2 px-4 rounded-lg transition">
                    {{ __('app.challenge.view_details') }}
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Active Author Filter Indicator -->
    @if(request('author'))
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">
                        {{ __('app.books.filter_active') }}
                    </p>
                    <p class="text-sm text-blue-700">
                        {{ __('app.books.showing_books_by') }}: <span class="font-semibold">{{ request('author') }}</span>
                        <span class="text-blue-600">({{ $books->total() }} {{ __('app.books.books_count') }})</span>
                    </p>
                </div>
            </div>
            <a href="{{ route('books.index', array_filter([
                    'status' => request('status'),
                    'sort' => request('sort'),
                    'search' => request('search')
                ])) }}"
               class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-md transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ __('app.books.clear_filter') }}
            </a>
        </div>
    </div>
    @endif

    <!-- Search and Bulk Actions -->
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <form action="{{ route('books.index') }}" method="GET" class="flex gap-4">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="{{ __('app.books.search_placeholder') }}"
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">

            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif

            @if(request('author'))
                <input type="hidden" name="author" value="{{ request('author') }}">
            @endif

            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">
                {{ __('app.books.search') }}
            </button>

            @if(request('search'))
                <a href="{{ route('books.index', request()->only(['status', 'sort', 'author'])) }}" class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300">
                    {{ __('app.books.clear') }}
                </a>
            @endif
        </form>

        <!-- Bulk Actions -->
        @if($books->isNotEmpty())
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="select-all" class="text-sm text-gray-700">{{ __('app.books.select_all') }}</label>
                <span id="selection-count" class="text-sm text-gray-600 ml-2"></span>
            </div>
            <div class="flex gap-2">
                <button type="button"
                        id="bulk-add-tags-btn"
                        class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    {{ __('app.books.add_tags') }}
                </button>
                <button type="button"
                        id="bulk-remove-tag-btn"
                        class="px-4 py-2 bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    {{ __('app.books.remove_tag') }}
                </button>
                <button type="button"
                        id="bulk-delete-btn"
                        class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    {{ __('app.books.delete_selected') }}
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Filter Tabs with Sort -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div class="flex space-x-4">
                <a href="{{ route('books.index', request()->only(['search', 'sort'])) }}" class="px-4 py-2 rounded-lg {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }}">
                    {{ __('app.books.all_books') }} <span class="ml-2 text-sm opacity-75">({{ $counts['all'] }})</span>
                </a>
                <a href="{{ route('books.index', array_merge(request()->only(['search', 'sort']), ['status' => 'want_to_read'])) }}" class="px-4 py-2 rounded-lg {{ request('status') === 'want_to_read' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }}">
                    {{ __('app.books.want_to_read') }} <span class="ml-2 text-sm opacity-75">({{ $counts['want_to_read'] }})</span>
                </a>
                <a href="{{ route('books.index', array_merge(request()->only(['search', 'sort']), ['status' => 'currently_reading'])) }}" class="px-4 py-2 rounded-lg {{ request('status') === 'currently_reading' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }}">
                    {{ __('app.books.currently_reading') }} <span class="ml-2 text-sm opacity-75">({{ $counts['currently_reading'] }})</span>
                </a>
                <a href="{{ route('books.index', array_merge(request()->only(['search', 'sort']), ['status' => 'read'])) }}" class="px-4 py-2 rounded-lg {{ request('status') === 'read' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }}">
                    {{ __('app.books.read') }} <span class="ml-2 text-sm opacity-75">({{ $counts['read'] }})</span>
                </a>
            </div>

            <!-- View Mode Toggle -->
            <div class="flex gap-1 border border-gray-300 rounded-lg p-1 bg-white">
                <form action="{{ route('books.toggle-view-mode') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="shelf" value="{{ request('status', 'all') }}">
                    <input type="hidden" name="view_mode" value="card">
                    <button type="submit"
                            class="p-2 rounded {{ $viewPref->view_mode === 'card' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }} transition-colors"
                            title="{{ __('app.books.view_card') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </button>
                </form>
                <form action="{{ route('books.toggle-view-mode') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="shelf" value="{{ request('status', 'all') }}">
                    <input type="hidden" name="view_mode" value="table">
                    <button type="submit"
                            class="p-2 rounded {{ $viewPref->view_mode === 'table' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }} transition-colors"
                            title="{{ __('app.books.view_table') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Column Settings (only visible in table view) -->
            @if($viewPref->view_mode === 'table')
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="px-3 py-1.5 border border-gray-300 rounded-lg bg-white text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    {{ __('app.books.columns') }}
                </button>

                <!-- Dropdown panel -->
                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-gray-900">{{ __('app.books.select_columns') }}</h3>
                            <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Predefined Column Sets -->
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.books.column_presets') }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                @php
                                    $columnSets = \App\Models\BookViewPreference::getColumnSets();
                                @endphp
                                @foreach($columnSets as $setName => $setColumns)
                                <button type="button"
                                        onclick="applyColumnSet({{ json_encode($setColumns) }})"
                                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                    {{ __('app.books.preset_' . $setName) }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <form id="column-settings-form" action="{{ route('books.update-column-settings') }}" method="POST">
                            @csrf
                            <input type="hidden" name="shelf" value="{{ request('status', 'all') }}">

                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @php
                                    $allColumns = \App\Models\BookViewPreference::getAllAvailableColumns();
                                    $visibleColumns = $viewPref->visible_columns ?? \App\Models\BookViewPreference::getDefaultColumns();
                                @endphp

                                @foreach($allColumns as $column)
                                <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox"
                                           name="visible_columns[]"
                                           value="{{ $column }}"
                                           {{ in_array($column, $visibleColumns) ? 'checked' : '' }}
                                           {{ in_array($column, ['title', 'actions']) ? 'disabled' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-700">
                                        {{ __('app.books.column_' . $column) }}
                                        @if(in_array($column, ['title', 'actions']))
                                        <span class="text-xs text-gray-500">({{ __('app.books.required') }})</span>
                                        @endif
                                    </span>
                                </label>
                                @endforeach
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
                                <button type="button"
                                        onclick="document.querySelectorAll('#column-settings-form input[type=checkbox]:not([disabled])').forEach(cb => cb.checked = true)"
                                        class="text-sm text-indigo-600 hover:text-indigo-800">
                                    {{ __('app.books.select_all') }}
                                </button>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                    {{ __('app.books.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @endif

            <!-- Sort Dropdown -->
            <form action="{{ route('books.index') }}" method="GET" class="inline-block">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <select name="sort"
                        onchange="this.form.submit()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                    <option value="added_at_desc" {{ request('sort', 'added_at_desc') === 'added_at_desc' ? 'selected' : '' }}>{{ __('app.books.sort_added_newest') }}</option>
                    <option value="added_at_asc" {{ request('sort') === 'added_at_asc' ? 'selected' : '' }}>{{ __('app.books.sort_added_oldest') }}</option>
                    <option value="title_asc" {{ request('sort') === 'title_asc' ? 'selected' : '' }}>{{ __('app.books.sort_title_az') }}</option>
                    <option value="title_desc" {{ request('sort') === 'title_desc' ? 'selected' : '' }}>{{ __('app.books.sort_title_za') }}</option>
                    <option value="author_asc" {{ request('sort') === 'author_asc' ? 'selected' : '' }}>{{ __('app.books.sort_author_az') }}</option>
                    <option value="author_desc" {{ request('sort') === 'author_desc' ? 'selected' : '' }}>{{ __('app.books.sort_author_za') }}</option>
                    <option value="published_date_desc" {{ request('sort') === 'published_date_desc' ? 'selected' : '' }}>{{ __('app.books.sort_date_newest') }}</option>
                    <option value="published_date_asc" {{ request('sort') === 'published_date_asc' ? 'selected' : '' }}>{{ __('app.books.sort_date_oldest') }}</option>
                </select>
            </form>
        </div>
    </div>

    @if($books->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500 mb-4">{{ __('app.books.no_books_found') }}</p>
        <a href="{{ route('books.create') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
            {{ __('app.books.add_first_book') }}
        </a>
    </div>
    @else

    @if($viewPref->view_mode === 'table')
        <!-- Table View -->
        @php
            $visibleColumns = $viewPref->visible_columns ?? \App\Models\BookViewPreference::getDefaultColumns();
        @endphp
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @foreach($visibleColumns as $column)
                            @php
                                // Define which columns are sortable and their field mappings
                                $sortableColumns = [
                                    'title' => 'title',
                                    'author' => 'author',
                                    'series' => 'series',
                                    'rating' => 'rating',
                                    'pages' => 'page_count',
                                    'current_page' => 'current_page',
                                    'publisher' => 'publisher',
                                    'published_date' => 'published_date',
                                    'purchase_date' => 'purchase_date',
                                    'purchase_price' => 'purchase_price',
                                    'date_added' => 'added_at',
                                    'date_started' => 'started_at',
                                    'date_finished' => 'finished_at',
                                ];
                                $isSortable = isset($sortableColumns[$column]);
                                $sortField = $sortableColumns[$column] ?? null;

                                // Current sort - parse it the same way as controller
                                $currentSort = request('sort', 'added_at_desc');
                                $currentSortParts = explode('_', $currentSort);
                                $currentDir = array_pop($currentSortParts);
                                $currentField = implode('_', $currentSortParts);

                                // Validate direction, default to desc if invalid
                                if (!in_array($currentDir, ['asc', 'desc'])) {
                                    $currentDir = 'desc';
                                }

                                // Is this column currently sorted?
                                $isCurrentSort = $sortField && $currentField === $sortField;

                                // Toggle direction
                                $newDir = ($isCurrentSort && $currentDir === 'asc') ? 'desc' : 'asc';
                                $newSort = $sortField ? $sortField . '_' . $newDir : null;
                            @endphp

                            <th scope="col" class="px-3 py-3 {{ $column === 'actions' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">
                                @if($isSortable)
                                <a href="{{ route('books.index', array_filter([
                                    'status' => request('status'),
                                    'search' => request('search'),
                                    'author' => request('author'),
                                    'sort' => $newSort,
                                ])) }}" class="flex items-center gap-1 hover:text-gray-700 group">
                                    <span>{{ __('app.books.column_' . $column) }}</span>
                                    @if($isCurrentSort)
                                        @if($currentDir === 'asc')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                        @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                        @endif
                                    @else
                                    <svg class="w-4 h-4 opacity-0 group-hover:opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                    @endif
                                </a>
                                @else
                                {{ __('app.books.column_' . $column) }}
                                @endif
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($books as $book)
                        <tr class="hover:bg-gray-50 transition">
                            @foreach($visibleColumns as $column)
                                @if($column === 'cover')
                                    <!-- Cover -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <a href="{{ route('books.show', $book) }}">
                                            @if($book->thumbnail_image)
                                            <img src="{{ $book->thumbnail_image }}" alt="{{ $book->title }}" class="h-16 w-12 object-cover rounded">
                                            @else
                                            <div class="h-16 w-12 bg-gray-200 rounded flex items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                            @endif
                                        </a>
                                    </td>
                                @elseif($column === 'title')
                                    <!-- Title -->
                                    <td class="px-3 py-4">
                                        <a href="{{ route('books.show', $book) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                            {{ $book->title }}
                                        </a>
                                    </td>
                                @elseif($column === 'author')
                                    <!-- Author -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        @if($book->author)
                                        <a href="{{ route('books.index', ['author' => $book->author]) }}" class="text-sm text-gray-700 hover:text-indigo-600">
                                            {{ $book->author }}
                                        </a>
                                        @else
                                        <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                @elseif($column === 'series')
                                    <!-- Series -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        @if($book->series)
                                        <a href="{{ route('books.series', ['series' => $book->series]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                                            {{ $book->series }}@if($book->series_position) #{{ $book->series_position }}@endif
                                        </a>
                                        @else
                                        <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                @elseif($column === 'status')
                                    <!-- Status -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $book->status === 'read' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $book->status === 'currently_reading' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $book->status === 'want_to_read' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ __('app.books.' . $book->status) }}
                                        </span>
                                    </td>
                                @elseif($column === 'rating')
                                    <!-- Rating -->
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        @if($book->rating)
                                        <div class="flex items-center text-sm">
                                            <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            <span class="ml-1 text-gray-700">{{ number_format($book->rating, 1) }}</span>
                                        </div>
                                        @else
                                        <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                @elseif($column === 'pages')
                                    <!-- Pages -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $book->page_count ?? '-' }}
                                    </td>
                                @elseif($column === 'current_page')
                                    <!-- Current Page -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $book->current_page ?? '-' }}
                                    </td>
                                @elseif($column === 'language')
                                    <!-- Language -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $book->language ? strtoupper($book->language) : '-' }}
                                    </td>
                                @elseif($column === 'publisher')
                                    <!-- Publisher -->
                                    <td class="px-3 py-4 text-sm text-gray-700">
                                        {{ $book->publisher ?? '-' }}
                                    </td>
                                @elseif($column === 'published_date')
                                    <!-- Published Date -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $book->published_date ? $book->published_date->format('Y') : '-' }}
                                    </td>
                                @elseif($column === 'isbn')
                                    <!-- ISBN -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">
                                        {{ $book->isbn13 ?? $book->isbn ?? '-' }}
                                    </td>
                                @elseif($column === 'format')
                                    <!-- Format -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $book->format ?? '-' }}
                                    </td>
                                @elseif($column === 'purchase_date')
                                    <!-- Purchase Date -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $book->purchase_date ? $book->purchase_date->format('M d, Y') : '-' }}
                                    </td>
                                @elseif($column === 'purchase_price')
                                    <!-- Purchase Price -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if($book->purchase_price)
                                            {{ $book->purchase_currency ?? '$' }}{{ number_format($book->purchase_price, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                @elseif($column === 'date_added')
                                    <!-- Date Added -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $book->added_at->format('M d, Y') }}
                                    </td>
                                @elseif($column === 'date_started')
                                    <!-- Date Started -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $book->started_at ? $book->started_at->format('M d, Y') : '-' }}
                                    </td>
                                @elseif($column === 'date_finished')
                                    <!-- Date Finished -->
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $book->finished_at ? $book->finished_at->format('M d, Y') : '-' }}
                                    </td>
                                @elseif($column === 'tags')
                                    <!-- Tags -->
                                    <td class="px-3 py-4">
                                        @if($book->tags->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($book->tags->take(3) as $tag)
                                            <span class="px-2 py-0.5 text-xs rounded-full" style="background-color: {{ $tag->color }}; color: white;">
                                                {{ $tag->name }}
                                            </span>
                                            @endforeach
                                            @if($book->tags->count() > 3)
                                            <span class="text-xs text-gray-500">+{{ $book->tags->count() - 3 }}</span>
                                            @endif
                                        </div>
                                        @else
                                        <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                @elseif($column === 'actions')
                                    <!-- Actions -->
                                    <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('books.show', $book) }}" class="text-blue-600 hover:text-blue-900" title="{{ __('app.books.view') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('books.edit', $book) }}" class="text-gray-600 hover:text-gray-900" title="{{ __('app.books.edit') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Card View (existing) -->
        <div style="display: grid !important; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important; gap: 1rem !important; width: 100% !important;">
            @foreach($books as $book)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition relative group">
            <!-- Checkbox for selection -->
            <div class="absolute top-2 left-2 z-10">
                <input type="checkbox" class="book-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-5 h-5" data-book-id="{{ $book->id }}">
            </div>

            <!-- Action buttons overlay -->
            <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                <a href="{{ route('books.show', $book) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-full shadow-lg"
                   title="{{ __('app.books.view') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </a>
                <a href="{{ route('books.edit', $book) }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white p-2 rounded-full shadow-lg"
                   title="{{ __('app.books.edit') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </a>
                <form action="{{ route('books.destroy', $book) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('{{ __('app.books.delete_confirm') }}')"
                            class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-full shadow-lg"
                            title="{{ __('app.books.delete') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
            </div>

            <a href="{{ route('books.show', $book) }}" class="block">
                @if($book->thumbnail_image)
                <img src="{{ $book->thumbnail_image }}" alt="{{ $book->title }}" class="w-full h-56 object-cover rounded-t-lg">
                @else
                <div class="w-full h-56 bg-gray-200 rounded-t-lg flex items-center justify-center">
                    <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                @endif
            </a>
            <div class="p-3">
                <h3 class="font-semibold text-gray-900 text-sm line-clamp-2 h-10">{{ $book->title }}</h3>
                @if($book->series)
                <a href="{{ route('books.series', ['series' => $book->series]) }}"
                   class="text-xs text-purple-600 hover:text-purple-800 hover:underline truncate mt-1 block">
                    {{ $book->series }}@if($book->series_position) #{{ $book->series_position }}@endif
                </a>
                @endif
                @if($book->author)
                <a href="{{ route('books.index', ['author' => $book->author]) }}"
                   class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline truncate mt-1 block">
                    {{ $book->author }}
                </a>
                @endif

                <form action="{{ route('books.status', $book) }}" method="POST" class="mt-2">
                    @csrf
                    @method('PATCH')
                    <select name="status"
                            onchange="this.form.submit()"
                            class="w-full text-xs px-2 py-1 border border-gray-300 rounded
                                   {{ $book->status === 'want_to_read' ? 'bg-yellow-50 text-yellow-800 border-yellow-300' : '' }}
                                   {{ $book->status === 'currently_reading' ? 'bg-blue-50 text-blue-800 border-blue-300' : '' }}
                                   {{ $book->status === 'read' ? 'bg-green-50 text-green-800 border-green-300' : '' }}">
                        <option value="want_to_read" {{ $book->status === 'want_to_read' ? 'selected' : '' }}>{{ __('app.books.want_to_read') }}</option>
                        <option value="currently_reading" {{ $book->status === 'currently_reading' ? 'selected' : '' }}>{{ __('app.books.currently_reading') }}</option>
                        <option value="read" {{ $book->status === 'read' ? 'selected' : '' }}>{{ __('app.books.read') }}</option>
                    </select>
                </form>

                @if($book->tags->isNotEmpty())
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach($book->tags as $tag)
                    @php
                        // Map colors to lighter backgrounds with darker text
                        $colorMap = [
                            '#ef4444' => ['bg' => '#fee2e2', 'text' => '#991b1b'], // Red
                            '#f97316' => ['bg' => '#ffedd5', 'text' => '#9a3412'], // Orange
                            '#eab308' => ['bg' => '#fef9c3', 'text' => '#854d0e'], // Yellow
                            '#22c55e' => ['bg' => '#dcfce7', 'text' => '#166534'], // Green
                            '#06b6d4' => ['bg' => '#cffafe', 'text' => '#155e75'], // Cyan
                            '#3b82f6' => ['bg' => '#dbeafe', 'text' => '#1e40af'], // Blue
                            '#6366f1' => ['bg' => '#e0e7ff', 'text' => '#3730a3'], // Indigo
                            '#a855f7' => ['bg' => '#f3e8ff', 'text' => '#6b21a8'], // Purple
                            '#ec4899' => ['bg' => '#fce7f3', 'text' => '#9f1239'], // Pink
                            '#64748b' => ['bg' => '#f1f5f9', 'text' => '#334155'], // Gray
                        ];
                        $colors = $colorMap[$tag->color] ?? ['bg' => '#e0e7ff', 'text' => '#3730a3'];
                    @endphp
                    <a href="{{ route('tags.show', $tag) }}"
                       class="px-2 py-0.5 rounded-full text-xs font-medium hover:opacity-80 transition-opacity"
                       style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
                        {{ $tag->name }}
                    </a>
                    @endforeach
                </div>
                @endif

                @if($book->status === 'currently_reading' && $book->page_count)
                <div class="mt-2">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span>{{ __('app.books.progress') }}</span>
                        <span>{{ $book->reading_progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $book->reading_progress }}%"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endif

    <div class="mt-6 flex items-center justify-between">
        <div>
            {{ $books->links() }}
        </div>

        @if($viewPref->view_mode === 'table')
        <form action="{{ route('books.index') }}" method="GET" class="inline-block">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if(request('author'))
                <input type="hidden" name="author" value="{{ request('author') }}">
            @endif
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            <select name="per_page"
                    onchange="this.form.submit()"
                    class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white text-sm">
                <option value="10" {{ request('per_page', $viewPref->per_page ?? 25) == 10 ? 'selected' : '' }}>10 {{ __('app.books.per_page') }}</option>
                <option value="25" {{ request('per_page', $viewPref->per_page ?? 25) == 25 ? 'selected' : '' }}>25 {{ __('app.books.per_page') }}</option>
                <option value="50" {{ request('per_page', $viewPref->per_page ?? 25) == 50 ? 'selected' : '' }}>50 {{ __('app.books.per_page') }}</option>
                <option value="100" {{ request('per_page', $viewPref->per_page ?? 25) == 100 ? 'selected' : '' }}>100 {{ __('app.books.per_page') }}</option>
            </select>
        </form>
        @endif
    </div>
</div>

<!-- Add Tags Modal -->
<div id="add-tags-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.books.add_tags_to_books') }}</h3>
            <div class="mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.books.select_tags') }}</label>
                <input type="text" id="tag-search" placeholder="{{ __('app.books.search_tags') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 mb-2">
                <div id="tags-list" class="max-h-64 overflow-y-auto border border-gray-300 rounded-md p-2">
                    @foreach($userTags as $tag)
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer tag-option">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 tag-checkbox">
                            <span class="ml-3 text-sm text-gray-700 tag-name">{{ $tag->name }}</span>
                            <span class="ml-auto w-4 h-4 rounded-full" style="background-color: {{ $tag->color }}"></span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="mt-4 flex justify-between items-center">
                <button type="button" id="select-all-tags" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('app.books.select_all') }}</button>
                <div class="flex gap-2">
                    <button type="button" id="cancel-add-tags" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">{{ __('app.books.cancel') }}</button>
                    <button type="button" id="confirm-add-tags" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">{{ __('app.books.add_tags') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Remove Tag Modal -->
<div id="remove-tag-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.books.remove_tag_from_books') }}</h3>
            <div class="mt-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.books.select_tag_to_remove') }}</label>
                <select id="tag-to-remove" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('app.books.select_tag') }}</option>
                </select>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" id="cancel-remove-tag" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">{{ __('app.books.cancel') }}</button>
                <button type="button" id="confirm-remove-tag" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">{{ __('app.books.remove_tag') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const bookCheckboxes = document.querySelectorAll('.book-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const bulkAddTagsBtn = document.getElementById('bulk-add-tags-btn');
    const bulkRemoveTagBtn = document.getElementById('bulk-remove-tag-btn');
    const selectionCount = document.getElementById('selection-count');

    // Select/deselect all
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            bookCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionButtons();
        });
    }

    // Update bulk action button states
    bookCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionButtons);
    });

    function updateBulkActionButtons() {
        const checkedBoxes = document.querySelectorAll('.book-checkbox:checked');
        const count = checkedBoxes.length;

        // Update button states
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = count === 0;
        if (bulkAddTagsBtn) bulkAddTagsBtn.disabled = count === 0;
        if (bulkRemoveTagBtn) bulkRemoveTagBtn.disabled = count === 0;

        // Update selection count
        if (selectionCount) {
            selectionCount.textContent = count > 0 ? `(${count} selected)` : '';
        }
    }

    // Bulk delete action
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.book-checkbox:checked');
            const bookIds = Array.from(checkedBoxes).map(cb => cb.dataset.bookId);

            if (bookIds.length === 0) {
                return;
            }

            if (!confirm('{{ __('app.books.bulk_delete_confirm', ['count' => '']) }}'.replace(':count', bookIds.length))) {
                return;
            }

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("books.bulk-delete") }}';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            // Add book IDs
            bookIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'book_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });
    }

    // Bulk Add Tags functionality
    const addTagsModal = document.getElementById('add-tags-modal');
    const tagSearch = document.getElementById('tag-search');
    const tagsList = document.getElementById('tags-list');
    const cancelAddTags = document.getElementById('cancel-add-tags');
    const confirmAddTags = document.getElementById('confirm-add-tags');
    const selectAllTagsBtn = document.getElementById('select-all-tags');

    if (bulkAddTagsBtn) {
        bulkAddTagsBtn.addEventListener('click', function() {
            // Clear search and uncheck all tags
            tagSearch.value = '';
            document.querySelectorAll('.tag-checkbox').forEach(cb => cb.checked = false);
            document.querySelectorAll('.tag-option').forEach(opt => opt.style.display = '');

            // Show modal
            addTagsModal.classList.remove('hidden');
        });
    }

    // Tag search functionality
    if (tagSearch) {
        tagSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.tag-option').forEach(option => {
                const tagName = option.querySelector('.tag-name').textContent.toLowerCase();
                option.style.display = tagName.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Select all tags
    if (selectAllTagsBtn) {
        selectAllTagsBtn.addEventListener('click', function() {
            const visibleCheckboxes = Array.from(document.querySelectorAll('.tag-option'))
                .filter(opt => opt.style.display !== 'none')
                .map(opt => opt.querySelector('.tag-checkbox'));

            const allChecked = visibleCheckboxes.every(cb => cb.checked);
            visibleCheckboxes.forEach(cb => cb.checked = !allChecked);
        });
    }

    if (cancelAddTags) {
        cancelAddTags.addEventListener('click', function() {
            addTagsModal.classList.add('hidden');
        });
    }

    if (confirmAddTags) {
        confirmAddTags.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.book-checkbox:checked');
            const bookIds = Array.from(checkedBoxes).map(cb => cb.dataset.bookId);
            const selectedTags = Array.from(document.querySelectorAll('.tag-checkbox:checked')).map(cb => cb.value);

            if (bookIds.length === 0 || selectedTags.length === 0) {
                alert('{{ __('app.books.select_books_and_tags') }}');
                return;
            }

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("books.bulk-add-tags") }}';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            // Add book IDs
            bookIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'book_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            // Add tag IDs
            selectedTags.forEach(tagId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'tag_ids[]';
                input.value = tagId;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });
    }

    // Bulk Remove Tag functionality
    const removeTagModal = document.getElementById('remove-tag-modal');
    const tagToRemove = document.getElementById('tag-to-remove');
    const cancelRemoveTag = document.getElementById('cancel-remove-tag');
    const confirmRemoveTag = document.getElementById('confirm-remove-tag');

    if (bulkRemoveTagBtn) {
        bulkRemoveTagBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.book-checkbox:checked');
            const bookIds = Array.from(checkedBoxes).map(cb => cb.dataset.bookId);

            // Get all tags from selected books
            const allTags = @json($userTags);
            const bookTagsMap = {};

            // Build a map of book tags from the page (we'll need to get this from the DOM)
            checkedBoxes.forEach(checkbox => {
                const bookCard = checkbox.closest('.bg-white');
                const tagLinks = bookCard ? bookCard.querySelectorAll('a[href*="/tags/"]') : [];
                const bookId = checkbox.dataset.bookId;
                bookTagsMap[bookId] = Array.from(tagLinks).map(link => {
                    const tagName = link.textContent.trim();
                    const tag = allTags.find(t => t.name === tagName);
                    return tag ? tag.id : null;
                }).filter(id => id !== null);
            });

            // Find tags that exist on at least one selected book
            const tagsOnSelectedBooks = new Set();
            Object.values(bookTagsMap).forEach(tags => {
                tags.forEach(tagId => tagsOnSelectedBooks.add(tagId));
            });

            // Populate dropdown with applicable tags
            tagToRemove.innerHTML = '<option value="">{{ __('app.books.select_tag') }}</option>';
            allTags.forEach(tag => {
                if (tagsOnSelectedBooks.has(tag.id)) {
                    const option = document.createElement('option');
                    option.value = tag.id;
                    option.textContent = tag.name;
                    tagToRemove.appendChild(option);
                }
            });

            // Show modal
            removeTagModal.classList.remove('hidden');
        });
    }

    if (cancelRemoveTag) {
        cancelRemoveTag.addEventListener('click', function() {
            removeTagModal.classList.add('hidden');
        });
    }

    if (confirmRemoveTag) {
        confirmRemoveTag.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.book-checkbox:checked');
            const bookIds = Array.from(checkedBoxes).map(cb => cb.dataset.bookId);
            const tagId = tagToRemove.value;

            if (bookIds.length === 0 || !tagId) {
                alert('{{ __('app.books.select_books_and_tag') }}');
                return;
            }

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("books.bulk-remove-tag") }}';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            // Add book IDs
            bookIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'book_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            // Add tag ID
            const tagInput = document.createElement('input');
            tagInput.type = 'hidden';
            tagInput.name = 'tag_id';
            tagInput.value = tagId;
            form.appendChild(tagInput);

            document.body.appendChild(form);
            form.submit();
        });
    }

    // Close modals on outside click
    [addTagsModal, removeTagModal].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }
    });

    // Apply column set preset
    window.applyColumnSet = function(columns) {
        // Uncheck all checkboxes first (except disabled ones)
        document.querySelectorAll('#column-settings-form input[type=checkbox]:not([disabled])').forEach(cb => {
            cb.checked = false;
        });

        // Check the columns in the preset
        columns.forEach(column => {
            const checkbox = document.querySelector(`#column-settings-form input[value="${column}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    };
});
</script>
@endpush
@endsection
