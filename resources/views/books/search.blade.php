@extends('layouts.app')

@section('title', 'Search Books')

@section('content')
<div class="px-4 sm:px-0">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Search Books</h1>

    @if($query)
        <div class="mb-6">
            <p class="text-gray-600">
                Search results for: <span class="font-semibold text-gray-900">"{{ $query }}"</span>
                @if(count($results) > 0)
                    <span class="text-sm text-gray-500">({{ count($results) }} results)</span>
                @endif
            </p>
        </div>

        @if(count($results) === 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-yellow-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <h3 class="text-lg font-medium text-yellow-800 mb-2">No books found</h3>
                <p class="text-yellow-700">
                    Try searching with a different ISBN, title, or author name.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($results as $book)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        @if($book['thumbnail_url'])
                            <div class="aspect-[2/3] bg-gray-100">
                                <img
                                    src="{{ $book['thumbnail_url'] }}"
                                    alt="{{ $book['title'] }}"
                                    class="w-full h-full object-cover"
                                >
                            </div>
                        @else
                            <div class="aspect-[2/3] bg-gray-100 flex items-center justify-center">
                                <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        @endif

                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 line-clamp-2 mb-1">
                                {{ $book['title'] }}
                            </h3>

                            @if($book['subtitle'])
                                <p class="text-sm text-gray-600 line-clamp-1 mb-2">
                                    {{ $book['subtitle'] }}
                                </p>
                            @endif

                            @if(!empty($book['authors']))
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ implode(', ', $book['authors']) }}
                                </p>
                            @endif

                            @if($book['published_date'])
                                <p class="text-xs text-gray-500 mb-2">
                                    {{ substr($book['published_date'], 0, 4) }}
                                    @if($book['publisher'])
                                        · {{ $book['publisher'] }}
                                    @endif
                                </p>
                            @endif

                            @if($book['page_count'])
                                <p class="text-xs text-gray-500 mb-3">
                                    {{ $book['page_count'] }} pages
                                </p>
                            @endif

                            <form action="{{ route('books.store-from-api') }}" method="POST">
                                @csrf
                                <input type="hidden" name="api_source" value="google">
                                <input type="hidden" name="external_id" value="{{ $book['google_books_id'] }}">
                                <input type="hidden" name="title" value="{{ $book['title'] }}">
                                <input type="hidden" name="author" value="{{ !empty($book['authors']) ? implode(', ', $book['authors']) : '' }}">
                                <input type="hidden" name="isbn" value="{{ $book['isbn'] }}">
                                <input type="hidden" name="isbn13" value="{{ $book['isbn13'] }}">
                                <input type="hidden" name="publisher" value="{{ $book['publisher'] }}">
                                <input type="hidden" name="published_date" value="{{ $book['published_date'] }}">
                                <input type="hidden" name="description" value="{{ $book['description'] }}">
                                <input type="hidden" name="page_count" value="{{ $book['page_count'] }}">
                                <input type="hidden" name="language" value="{{ $book['language'] }}">
                                <input type="hidden" name="cover_url" value="{{ $book['cover_url'] ?? $book['thumbnail_url'] }}">
                                <input type="hidden" name="thumbnail" value="{{ $book['thumbnail_url'] }}">
                                <input type="hidden" name="status" value="want_to_read">

                                <button
                                    type="submit"
                                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                                >
                                    Add to Library
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Start searching</h3>
            <p class="text-gray-600 mb-4">
                Use the search bar above to find books by ISBN, title, or author.
            </p>
            <div class="max-w-md mx-auto text-left">
                <p class="text-sm text-gray-500 mb-2">Examples:</p>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• <code class="bg-gray-100 px-2 py-1 rounded">9780316769488</code> (ISBN)</li>
                    <li>• <code class="bg-gray-100 px-2 py-1 rounded">The Catcher in the Rye</code> (Title)</li>
                    <li>• <code class="bg-gray-100 px-2 py-1 rounded">J.D. Salinger</code> (Author)</li>
                </ul>
            </div>
        </div>
    @endif
</div>
@endsection
