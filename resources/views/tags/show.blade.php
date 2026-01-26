@extends('layouts.app')

@section('title', $tag->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ localeRoute('tags.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Tags
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $tag->name }}</h1>
                    @if($tag->is_default)
                    <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-600 rounded">Default</span>
                    @endif
                </div>
                @if($tag->description)
                <p class="text-gray-600 mt-2">{{ $tag->description }}</p>
                @endif
                <p class="text-sm text-gray-500 mt-2">
                    {{ $books->total() }} {{ $books->total() === 1 ? 'book' : 'books' }} in this tag
                </p>
            </div>
            <div class="flex gap-2">
                @if(!$tag->is_default)
                <a href="{{ localeRoute('tags.edit', $tag) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium">
                    Edit Tag
                </a>
                @endif
            </div>
        </div>
    </div>

    @if($books->isEmpty())
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-900">No books in this tag</h3>
        <p class="mt-1 text-gray-500">Add books to this tag from the books page.</p>
        <div class="mt-6">
            <a href="{{ localeRoute('books.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Browse Books
            </a>
        </div>
    </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
        @foreach($books as $book)
        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden group relative">
            <!-- Remove from tag button -->
            <form action="{{ localeRoute('tags.remove-book', [$tag, $book]) }}" method="POST" class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-full shadow-lg" title="Remove from tag">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </form>

            <a href="{{ localeRoute('books.show', $book) }}">
                @if($book->thumbnail_image)
                <img src="{{ $book->thumbnail_image }}" alt="{{ $book->title }}" class="w-full h-64 object-cover">
                @else
                <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                    <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                @endif
            </a>
            <div class="p-3">
                <h3 class="font-semibold text-gray-900 text-sm line-clamp-2 h-10">{{ $book->title }}</h3>
                @if($book->author)
                <p class="text-xs text-gray-600 truncate mt-1">{{ $book->author }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $books->links() }}
    </div>
    @endif
</div>
@endsection
