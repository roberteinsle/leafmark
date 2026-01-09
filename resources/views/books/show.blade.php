@extends('layouts.app')

@section('title', $book->title)

@section('content')
<div class="px-4 max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('books.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Books
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
                        <p class="text-xl text-gray-600 mt-2">by {{ $book->author }}</p>
                        @endif
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('books.edit', $book) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Are you sure you want to delete this book?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-6">
                    <form method="POST" action="{{ route('books.status', $book) }}">
                        @csrf
                        @method('PATCH')
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reading Status</label>
                        <select name="status" onchange="this.form.submit()" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="want_to_read" {{ $book->status === 'want_to_read' ? 'selected' : '' }}>Want to Read</option>
                            <option value="currently_reading" {{ $book->status === 'currently_reading' ? 'selected' : '' }}>Currently Reading</option>
                            <option value="read" {{ $book->status === 'read' ? 'selected' : '' }}>Read</option>
                        </select>
                    </form>
                </div>

                @if($book->status === 'currently_reading' && $book->page_count)
                <div class="mt-6">
                    <form method="POST" action="{{ route('books.progress', $book) }}" class="flex items-end space-x-4">
                        @csrf
                        @method('PATCH')
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Page</label>
                            <input type="number" name="current_page" value="{{ $book->current_page }}" min="0" max="{{ $book->page_count }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                            Update
                        </button>
                    </form>
                    <div class="mt-4">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Progress</span>
                            <span>{{ $book->reading_progress }}% ({{ $book->current_page }}/{{ $book->page_count }} pages)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-indigo-600 h-3 rounded-full" style="width: {{ $book->reading_progress }}%"></div>
                        </div>
                    </div>

                    @if($book->progressHistory->isNotEmpty())
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Reading Progress History</h3>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($book->progressHistory as $entry)
                            <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded text-sm">
                                <div class="flex items-center space-x-3">
                                    <span class="font-medium text-gray-900">Page {{ $entry->page_number }}</span>
                                    <span class="text-gray-500">{{ $entry->recorded_at->format('M d, Y H:i') }}</span>
                                </div>
                                <form method="POST" action="{{ route('books.progress.delete', [$book, $entry->id]) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Delete this progress entry? Current page will revert to the previous entry.')"
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
                    @endif
                </div>
                @endif

                <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                    @if($book->format)
                    <div>
                        <span class="text-gray-600">Format:</span>
                        <span class="ml-2 font-medium">{{ ucwords(str_replace('_', ' ', $book->format)) }}</span>
                    </div>
                    @endif
                    @if($book->isbn)
                    <div>
                        <span class="text-gray-600">ISBN:</span>
                        <span class="ml-2 font-medium">{{ $book->isbn }}</span>
                    </div>
                    @endif
                    @if($book->isbn13)
                    <div>
                        <span class="text-gray-600">ISBN-13:</span>
                        <span class="ml-2 font-medium">{{ $book->isbn13 }}</span>
                    </div>
                    @endif
                    @if($book->publisher)
                    <div>
                        <span class="text-gray-600">Publisher:</span>
                        <span class="ml-2 font-medium">{{ $book->publisher }}</span>
                    </div>
                    @endif
                    @if($book->published_date)
                    <div>
                        <span class="text-gray-600">Published:</span>
                        <span class="ml-2 font-medium">{{ \Carbon\Carbon::parse($book->published_date)->format('Y') }}</span>
                    </div>
                    @endif
                    @if($book->page_count)
                    <div>
                        <span class="text-gray-600">Pages:</span>
                        <span class="ml-2 font-medium">{{ $book->page_count }}</span>
                    </div>
                    @endif
                    @if($book->language)
                    <div>
                        <span class="text-gray-600">Language:</span>
                        <span class="ml-2 font-medium">{{ strtoupper($book->language) }}</span>
                    </div>
                    @endif
                    @if($book->purchase_date)
                    <div>
                        <span class="text-gray-600">Purchase Date:</span>
                        <span class="ml-2 font-medium">{{ $book->purchase_date->format('M d, Y') }}</span>
                    </div>
                    @endif
                    @if($book->purchase_price)
                    <div>
                        <span class="text-gray-600">Purchase Price:</span>
                        <span class="ml-2 font-medium">{{ $book->purchase_currency }} {{ number_format($book->purchase_price, 2) }}</span>
                    </div>
                    @endif
                </div>

                @if($book->description)
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Description</h2>
                    <div class="text-gray-700 leading-relaxed prose max-w-none">{!! nl2br(e($book->description)) !!}</div>
                </div>
                @endif

                <!-- Edition Identifiers Section -->
                @if($book->openlibrary_edition_id || $book->goodreads_id || $book->librarything_id)
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Edition Identifiers</h2>
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

                <!-- Update from External Source -->
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Update from External Source</h2>
                    <form method="POST" action="{{ route('books.update-from-url', $book) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700 mb-1">
                                Source
                            </label>
                            <select
                                name="source"
                                id="source"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 mb-3"
                                onchange="updatePlaceholder()"
                            >
                                <option value="openlibrary">OpenLibrary</option>
                                <option value="googlebooks">Google Books</option>
                                <option value="amazon">Amazon</option>
                            </select>
                        </div>
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-700 mb-1">
                                URL or ID
                            </label>
                            <input
                                type="text"
                                name="url"
                                id="url"
                                placeholder="https://openlibrary.org/books/OL9064566M/..."
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            >
                            <p id="help-text" class="mt-1 text-xs text-gray-500">
                                Paste an OpenLibrary edition URL to auto-update this book's information and cover
                            </p>
                        </div>
                        <button
                            type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm"
                        >
                            Update Book Info
                        </button>
                    </form>
                    <script>
                        function updatePlaceholder() {
                            const source = document.getElementById('source').value;
                            const urlInput = document.getElementById('url');
                            const helpText = document.getElementById('help-text');

                            if (source === 'openlibrary') {
                                urlInput.placeholder = 'https://openlibrary.org/books/OL9064566M/...';
                                helpText.textContent = 'Paste an OpenLibrary edition URL to auto-update this book\'s information and cover';
                            } else if (source === 'googlebooks') {
                                urlInput.placeholder = 'https://books.google.com/books?id=VOLUME_ID or just VOLUME_ID';
                                helpText.textContent = 'Paste a Google Books URL or Volume ID (e.g., nggnmAEACAAJ)';
                            } else if (source === 'amazon') {
                                urlInput.placeholder = 'https://www.amazon.com/dp/ASIN or just ASIN/ISBN';
                                helpText.textContent = 'Paste an Amazon URL or ASIN/ISBN';
                            }
                        }
                    </script>
                </div>

                <!-- Tags Section -->
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Tags</h2>

                    @if($book->tags->isEmpty())
                    <p class="text-sm text-gray-500 mb-3">No tags added yet</p>
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
                                <option value="">Add a tag...</option>
                                @foreach($availableTags as $availableTag)
                                <option value="{{ route('tags.add-book', [$availableTag, $book]) }}">{{ $availableTag->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="addTag()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                                Add
                            </button>
                        </div>
                    </form>
                    @endif

                    <a href="{{ route('tags.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 mt-2 inline-block">
                        Manage Tags →
                    </a>
                </div>

                <div class="mt-6 text-sm text-gray-500">
                    <p>Added: {{ $book->added_at->format('M d, Y') }}</p>
                    @if($book->started_at)
                    <p>Started: {{ $book->started_at->format('M d, Y') }}</p>
                    @endif
                    @if($book->finished_at)
                    <p>Finished: {{ $book->finished_at->format('M d, Y') }}</p>
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
