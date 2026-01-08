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
                @if($book->cover_image)
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
                    <p class="text-gray-700 leading-relaxed">{{ $book->description }}</p>
                </div>
                @endif

                <!-- Tags Section -->
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Tags</h2>

                    @if($book->tags->isEmpty())
                    <p class="text-sm text-gray-500 mb-3">No tags added yet</p>
                    @else
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($book->tags as $tag)
                        <div class="flex items-center gap-2 text-white px-3 py-1 rounded-full text-sm" style="background-color: {{ $tag->color ?? '#6366f1' }}">
                            <a href="{{ route('tags.show', $tag) }}" class="hover:underline">{{ $tag->name }}</a>
                            <form action="{{ route('tags.remove-book', [$tag, $book]) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hover:bg-white/20 rounded-full p-0.5">
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
</script>
@endpush
