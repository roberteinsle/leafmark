@extends('layouts.app')

@section('title', $book->title)

@section('content')
<div class="px-4 max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('books.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('app.books.back_to_books') }}
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="md:flex">
            <div class="md:flex-shrink-0 md:w-64">
                @if($book->covers->count() > 0)
                    <!-- Multiple Covers Gallery -->
                    <div class="relative">
                        <div id="cover-gallery" class="w-full h-96">
                            @foreach($book->covers as $index => $cover)
                            <img src="{{ $cover->url }}"
                                 alt="{{ $book->title }}"
                                 data-cover-index="{{ $index }}"
                                 class="cover-slide absolute inset-0 w-full h-96 object-cover {{ $index === 0 ? '' : 'hidden' }}">
                            @endforeach
                        </div>

                        @if($book->covers->count() > 1)
                        <!-- Gallery Navigation -->
                        <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2">
                            @foreach($book->covers as $index => $cover)
                            <button onclick="showCover({{ $index }})"
                                    class="cover-dot w-2 h-2 rounded-full {{ $index === 0 ? 'bg-white' : 'bg-white/50' }}"></button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                @elseif($book->cover_image)
                    <img src="{{ $book->cover_image }}" alt="{{ $book->title }}" class="w-full h-96 object-cover">
                @else
                <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                    <svg class="h-32 w-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                @endif
            </div>
            <div class="p-8 flex-1">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $book->title }}</h1>
                        @if($book->series)
                        <p class="text-lg text-indigo-600 mt-1">
                            <a href="{{ route('books.series', ['series' => $book->series]) }}" class="hover:underline">
                                {{ $book->series }}@if($book->series_position) #{{ $book->series_position }}@endif
                            </a>
                        </p>
                        @endif
                        @if($book->author)
                        <p class="text-xl text-gray-600 mt-2">
                            {{ __('app.books.by_author') }}
                            <a href="{{ route('books.index', ['author' => $book->author]) }}"
                               class="text-indigo-600 hover:text-indigo-800 hover:underline font-medium transition-colors">
                                {{ $book->author }}
                            </a>
                        </p>
                        @endif
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('books.edit', $book) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
                            {{ __('app.books.edit') }}
                        </a>
                        <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('{{ __('app.books.delete_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                                {{ __('app.books.delete') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-6">
                    <form method="POST" action="{{ route('books.status', $book) }}">
                        @csrf
                        @method('PATCH')
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.books.reading_status') }}</label>
                        <select name="status" onchange="this.form.submit()" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="want_to_read" {{ $book->status === 'want_to_read' ? 'selected' : '' }}>{{ __('app.books.want_to_read') }}</option>
                            <option value="currently_reading" {{ $book->status === 'currently_reading' ? 'selected' : '' }}>{{ __('app.books.currently_reading') }}</option>
                            <option value="read" {{ $book->status === 'read' ? 'selected' : '' }}>{{ __('app.books.read') }}</option>
                        </select>
                    </form>
                </div>

                <!-- Tabs Navigation -->
                <div class="mt-8 border-b border-gray-200" x-data="{ activeTab: 'details' }">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button @click="activeTab = 'details'"
                                :class="activeTab === 'details' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            {{ __('app.books.tab_details') }}
                        </button>
                        @if($book->status === 'currently_reading' && $book->page_count)
                        <button @click="activeTab = 'progress'"
                                :class="activeTab === 'progress' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                            {{ __('app.books.tab_progress') }}
                        </button>
                        @endif
                    </nav>

                    <!-- Details Tab -->
                    <div x-show="activeTab === 'details'" class="mt-6">

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            @if($book->format)
                            <div>
                                <span class="text-gray-600">{{ __('app.books.format_label') }}:</span>
                                <span class="ml-2 font-medium">{{ ucwords(str_replace('_', ' ', $book->format)) }}</span>
                            </div>
                            @endif
                            @if($book->isbn)
                            <div>
                                <span class="text-gray-600">{{ __('app.books.isbn') }}:</span>
                                <span class="ml-2 font-medium">{{ $book->isbn }}</span>
                            </div>
                            @endif
                            @if($book->isbn13)
                            <div>
                                <span class="text-gray-600">{{ __('app.books.isbn13') }}:</span>
                                <span class="ml-2 font-medium">{{ $book->isbn13 }}</span>
                            </div>
                            @endif
                            @if($book->publisher)
                            <div>
                                <span class="text-gray-600">{{ __('app.books.publisher') }}:</span>
                                <span class="ml-2 font-medium">{{ $book->publisher }}</span>
                            </div>
                            @endif
                            @if($book->published_date)
                            <div>
                                <span class="text-gray-600">{{ __('app.books.published_date') }}:</span>
                                <span class="ml-2 font-medium">{{ \Carbon\Carbon::parse($book->published_date)->format('Y') }}</span>
                            </div>
                            @endif
                            @if($book->page_count)
                            <div>
                                <span class="text-gray-600">{{ __('app.books.pages') }}:</span>
                                <span class="ml-2 font-medium">{{ $book->page_count }}</span>
                            </div>
                            @endif
                            @if($book->language)
                            <div>
                                <span class="text-gray-600">{{ __('app.books.language') }}:</span>
                                <span class="ml-2 font-medium">{{ strtoupper($book->language) }}</span>
                            </div>
                            @endif
                            @if($book->purchase_date)
                            <div>
                                <span class="text-gray-600">{{ __('app.books.purchase_date') }}:</span>
                                <span class="ml-2 font-medium">{{ $book->purchase_date->format('M d, Y') }}</span>
                            </div>
                            @endif
                            @if($book->purchase_price)
                            <div>
                                <span class="text-gray-600">{{ __('app.books.purchase_price') }}:</span>
                                <span class="ml-2 font-medium">{{ $book->purchase_currency }} {{ number_format($book->purchase_price, 2) }}</span>
                            </div>
                            @endif
                        </div>

                        @if($book->description)
                        <div class="mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.books.description') }}</h2>
                            <div class="text-gray-700 leading-relaxed prose max-w-none">{!! nl2br(e($book->description)) !!}</div>
                        </div>
                        @endif

                        <!-- Rating and Review Section -->
                        <div class="mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('app.books.your_rating') }}</h2>
                            <form method="POST" action="{{ route('books.update-rating', $book) }}" x-data="{ rating: {{ $book->rating ?? 0 }} }">
                                @csrf
                                @method('PATCH')

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.books.rating_label') }}</label>
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                        <button type="button"
                                                @click="rating = {{ $i * 0.5 }}"
                                                class="focus:outline-none transition-transform hover:scale-110">
                                            <svg :class="rating >= {{ $i * 0.5 }} ? 'text-yellow-400' : 'text-gray-300'"
                                                 class="w-8 h-8 fill-current"
                                                 viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </button>
                                        @endfor
                                        <span class="ml-3 text-sm text-gray-600" x-text="rating > 0 ? rating + ' {{ __('app.books.stars') }}' : '{{ __('app.books.no_rating') }}'"></span>
                                    </div>
                                    <input type="hidden" name="rating" :value="rating">
                                </div>

                                <div class="mb-4">
                                    <label for="review" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.books.review_label') }}</label>
                                    <textarea name="review"
                                              id="review"
                                              rows="4"
                                              placeholder="{{ __('app.books.review_placeholder') }}"
                                              class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('review', $book->review) }}</textarea>
                                </div>

                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                                    {{ __('app.books.save_rating') }}
                                </button>
                            </form>
                        </div>

                        <!-- Edition Identifiers Section -->
                        @if($book->openlibrary_edition_id || $book->goodreads_id || $book->librarything_id)
                        <div class="mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('app.books.edition_identifiers') }}</h2>
                            <div class="space-y-2 text-sm">
                                @if($book->openlibrary_edition_id)
                                <div>
                                    <span class="text-gray-600">OL:</span>
                                    <a href="{{ $book->openlibrary_url ?? 'https://openlibrary.org/books/' . $book->openlibrary_edition_id }}"
                                       target="_blank"
                                       class="ml-2 text-indigo-600 hover:text-indigo-700 hover:underline">
                                        {{ $book->openlibrary_edition_id }}
                                        <svg class="inline w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>
                                @endif
                                @if($book->goodreads_id)
                                <div>
                                    <span class="text-gray-600">Goodreads:</span>
                                    <a href="https://www.goodreads.com/book/show/{{ $book->goodreads_id }}"
                                       target="_blank"
                                       class="ml-2 text-indigo-600 hover:text-indigo-700 hover:underline">
                                        {{ $book->goodreads_id }}
                                        <svg class="inline w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>
                                @endif
                                @if($book->librarything_id)
                                <div>
                                    <span class="text-gray-600">LibraryThing:</span>
                                    <a href="https://www.librarything.com/work/{{ $book->librarything_id }}"
                                       target="_blank"
                                       class="ml-2 text-indigo-600 hover:text-indigo-700 hover:underline">
                                        {{ $book->librarything_id }}
                                        <svg class="inline w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Tags Section -->
                        <div class="mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('app.books.tags_section') }}</h2>

                            @if($book->tags->isEmpty())
                            <p class="text-sm text-gray-500 mb-3">{{ __('app.books.no_tags_yet') }}</p>
                            @else
                            <div class="flex flex-wrap gap-2 mb-3">
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
                                <div class="flex items-center gap-2 px-3 py-1 rounded-full text-sm" style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}">
                                    <a href="{{ route('tags.show', $tag) }}" class="hover:underline" style="color: {{ $colors['text'] }}">{{ $tag->name }}</a>
                                    <form action="{{ route('tags.remove-book', [$tag, $book]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hover:opacity-70 rounded-full p-0.5">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            <!-- Add Tag Dropdown -->
                            @php
                                $availableTags = auth()->user()->tags()->whereNotIn('id', $book->tags->pluck('id'))->get();
                            @endphp

                            @if($availableTags->isNotEmpty())
                            <form action="" method="POST" id="add-tag-form">
                                @csrf
                                <div class="flex gap-2">
                                    <select id="tag-select" class="block px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">{{ __('app.books.add_a_tag') }}</option>
                                        @foreach($availableTags as $availableTag)
                                        <option value="{{ route('tags.add-book', [$availableTag, $book]) }}">{{ $availableTag->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="addTag()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                                        {{ __('app.books.add') }}
                                    </button>
                                </div>
                            </form>
                            @endif

                            <a href="{{ route('tags.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 mt-2 inline-block">
                                {{ __('app.books.manage_tags') }} →
                            </a>
                        </div>

                        <div class="mt-6 text-sm text-gray-500">
                            <p>{{ __('app.books.added') }}: {{ $book->added_at->format('M d, Y') }}</p>
                            @if($book->started_at)
                            <p>{{ __('app.books.started') }}: {{ $book->started_at->format('M d, Y') }}</p>
                            @endif
                            @if($book->finished_at)
                            <p>{{ __('app.books.finished') }}: {{ $book->finished_at->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Tab -->
                    @if($book->status === 'currently_reading' && $book->page_count)
                    <div x-show="activeTab === 'progress'" class="mt-6">
                        <form method="POST" action="{{ route('books.progress', $book) }}" class="flex items-end space-x-4">
                            @csrf
                            @method('PATCH')
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.books.current_page_label') }}</label>
                                <input type="number" name="current_page" value="{{ $book->current_page }}" min="0" max="{{ $book->page_count }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                                {{ __('app.books.update') }}
                            </button>
                        </form>

                        <div class="mt-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>{{ __('app.books.progress') }}</span>
                                <span>{{ $book->reading_progress }}% ({{ $book->current_page }}/{{ $book->page_count }} {{ __('app.books.pages') }})</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-indigo-600 h-3 rounded-full transition-all duration-300" style="width: {{ $book->reading_progress }}%"></div>
                            </div>
                        </div>

                        @if($book->progressHistory->isNotEmpty())
                        <!-- Progress Bar Chart -->
                        <div class="mt-8">
                            <h3 class="text-sm font-medium text-gray-700 mb-4">{{ __('app.books.progress_chart') }}</h3>
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <div class="flex items-end justify-between h-64 gap-2">
                                    @php
                                        $maxPage = $book->page_count;
                                        $history = $book->progressHistory->sortBy('recorded_at')->take(10);
                                    @endphp
                                    @foreach($history as $index => $entry)
                                    @php
                                        $heightPercent = ($entry->page_number / $maxPage) * 100;
                                    @endphp
                                    <div class="flex-1 flex flex-col items-center group relative">
                                        <div class="w-full bg-indigo-500 rounded-t transition-all duration-300 hover:bg-indigo-600"
                                             style="height: {{ $heightPercent }}%"
                                             title="{{ __('app.books.page') }} {{ $entry->page_number }} - {{ $entry->recorded_at->format('M d, Y') }}">
                                        </div>
                                        <!-- Tooltip -->
                                        <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">
                                            <div>{{ __('app.books.page') }} {{ $entry->page_number }}</div>
                                            <div>{{ $entry->recorded_at->format('M d, H:i') }}</div>
                                        </div>
                                        <span class="text-xs text-gray-500 mt-2 transform -rotate-45 origin-top-left">
                                            {{ $entry->recorded_at->format('M d') }}
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-300 mt-2 pt-2">
                                    <div class="flex justify-between text-xs text-gray-500">
                                        <span>{{ __('app.books.progress_over_time') }}</span>
                                        <span>{{ __('app.books.max_pages', ['count' => $maxPage]) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress History List -->
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">{{ __('app.books.reading_progress_history') }}</h3>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($book->progressHistory as $entry)
                                <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded text-sm hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center space-x-3">
                                        <span class="font-medium text-gray-900">{{ __('app.books.page') }} {{ $entry->page_number }}</span>
                                        <span class="text-gray-500">{{ $entry->recorded_at->format('M d, Y H:i') }}</span>
                                        <div class="flex-1 bg-gray-200 rounded-full h-1.5 max-w-xs">
                                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ ($entry->page_number / $book->page_count) * 100 }}%"></div>
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('books.progress.delete', [$book, $entry->id]) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('{{ __('app.books.delete_progress_confirm') }}')"
                                                class="text-red-600 hover:text-red-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="mt-8 text-center py-12 bg-gray-50 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">{{ __('app.books.no_progress_history') }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ __('app.books.update_progress_to_see_chart') }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addTag() {
    const select = document.getElementById('tag-select');
    const url = select.value;

    if (url) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;

        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Cover gallery navigation
let currentCover = 0;
function showCover(index) {
    const slides = document.querySelectorAll('.cover-slide');
    const dots = document.querySelectorAll('.cover-dot');

    slides.forEach((slide, i) => {
        if (i === index) {
            slide.classList.remove('hidden');
        } else {
            slide.classList.add('hidden');
        }
    });

    dots.forEach((dot, i) => {
        if (i === index) {
            dot.classList.remove('bg-white/50');
            dot.classList.add('bg-white');
        } else {
            dot.classList.remove('bg-white');
            dot.classList.add('bg-white/50');
        }
    });

    currentCover = index;
}
</script>
@endpush
