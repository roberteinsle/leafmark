@extends('layouts.app')

@section('title', $series)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('books.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center mb-4">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Books
        </a>
        <h1 class="text-3xl font-bold text-gray-900">{{ $series }}</h1>
        <p class="text-gray-600 mt-2">{{ $books->count() }} {{ $books->count() === 1 ? 'book' : 'books' }} in this series</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($books as $book)
        <a href="{{ route('books.show', $book) }}" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            @if($book->cover_image)
                <img src="{{ $book->cover_image }}" alt="{{ $book->title }}" class="w-full h-64 object-cover">
            @else
                <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                    <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            @endif

            <div class="p-4">
                <div class="flex items-start justify-between mb-2">
                    <h2 class="text-lg font-semibold text-gray-900 line-clamp-2 flex-1">
                        @if($book->series_position)
                            <span class="text-indigo-600">#{{ $book->series_position }}</span>
                        @endif
                        {{ $book->title }}
                    </h2>
                </div>

                @if($book->author)
                    <p class="text-sm text-gray-600 mb-2">{{ $book->author }}</p>
                @endif

                <div class="flex items-center gap-2 mt-3">
                    @if($book->status === 'want_to_read')
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Want to Read</span>
                    @elseif($book->status === 'currently_reading')
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Reading</span>
                    @elseif($book->status === 'read')
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Read</span>
                    @endif
                </div>

                @if($book->status === 'currently_reading' && $book->page_count)
                    <div class="mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $book->reading_progress }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $book->reading_progress }}%</p>
                    </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
