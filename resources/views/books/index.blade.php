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

            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">
                {{ __('app.books.search') }}
            </button>

            @if(request('search'))
                <a href="{{ route('books.index', request()->only(['status', 'sort'])) }}" class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300">
                    {{ __('app.books.clear') }}
                </a>
            @endif
        </form>

        <!-- Bulk Delete Form -->
        @if($books->isNotEmpty())
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="select-all" class="text-sm text-gray-700">{{ __('app.books.select_all') }}</label>
            </div>
            <button type="button"
                    id="bulk-delete-btn"
                    class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                {{ __('app.books.delete_selected') }}
            </button>
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
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
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
                <img src="{{ $book->thumbnail_image }}" alt="{{ $book->title }}" class="w-full h-64 object-cover rounded-t-lg">
                @else
                <div class="w-full h-64 bg-gray-200 rounded-t-lg flex items-center justify-center">
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

    <div class="mt-6">
        {{ $books->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const bookCheckboxes = document.querySelectorAll('.book-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

    // Select/deselect all
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            bookCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }

    // Update bulk delete button state
    bookCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkDeleteButton);
    });

    function updateBulkDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.book-checkbox:checked');
        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = checkedBoxes.length === 0;
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
});
</script>
@endpush
@endsection
