@extends('layouts.app')

@section('title', 'Books')

@section('content')
<div class="px-4">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Books</h1>
        <a href="{{ route('books.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg">
            Add Book
        </a>
    </div>

    <div class="mb-6">
        <div class="flex space-x-4">
            <a href="{{ route('books.index') }}" class="px-4 py-2 rounded-lg {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }}">
                All Books
            </a>
            <a href="{{ route('books.index', ['status' => 'want_to_read']) }}" class="px-4 py-2 rounded-lg {{ request('status') === 'want_to_read' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }}">
                Want to Read
            </a>
            <a href="{{ route('books.index', ['status' => 'currently_reading']) }}" class="px-4 py-2 rounded-lg {{ request('status') === 'currently_reading' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }}">
                Currently Reading
            </a>
            <a href="{{ route('books.index', ['status' => 'read']) }}" class="px-4 py-2 rounded-lg {{ request('status') === 'read' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }}">
                Read
            </a>
        </div>
    </div>

    @if($books->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500 mb-4">No books found. Start building your library!</p>
        <a href="{{ route('books.create') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
            Add your first book
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($books as $book)
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
            <a href="{{ route('books.show', $book) }}" class="block">
                @if($book->thumbnail)
                <img src="{{ $book->thumbnail }}" alt="{{ $book->title }}" class="w-full h-64 object-cover rounded-t-lg">
                @else
                <div class="w-full h-64 bg-gray-200 rounded-t-lg flex items-center justify-center">
                    <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                @endif
            </a>
            <div class="p-4">
                <h3 class="font-semibold text-gray-900 truncate">{{ $book->title }}</h3>
                @if($book->author)
                <p class="text-sm text-gray-600 truncate">{{ $book->author }}</p>
                @endif

                <div class="mt-3">
                    @if($book->status === 'want_to_read')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Want to Read
                    </span>
                    @elseif($book->status === 'currently_reading')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Reading
                    </span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Read
                    </span>
                    @endif
                </div>

                @if($book->status === 'currently_reading' && $book->page_count)
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span>Progress</span>
                        <span>{{ $book->reading_progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $book->reading_progress }}%"></div>
                    </div>
                </div>
                @endif

                <div class="mt-4 flex space-x-2">
                    <a href="{{ route('books.show', $book) }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-2 rounded">
                        View
                    </a>
                    <a href="{{ route('books.edit', $book) }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-2 rounded">
                        Edit
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $books->links() }}
    </div>
    @endif
</div>
@endsection
