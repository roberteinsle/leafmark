@extends('layouts.app')

@section('title', __('app.import.preview_title'))

@section('content')
<div class="px-4 max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.import.preview_title') }}</h1>
        <p class="mt-2 text-gray-600">{{ __('app.import.preview_description') }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ $filename }}</h2>
            <p class="text-gray-600">{{ __('app.import.total_books_found', ['count' => $total_rows]) }}</p>
        </div>

        @if(count($preview) > 0)
        <div class="mb-6">
            <h3 class="font-medium text-gray-900 mb-3">{{ __('app.import.preview_first_books') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.books.title_label') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.books.author') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.books.status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.books.rating_label') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($preview as $book)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $book['title'] ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $book['author'] ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $book['exclusive shelf'] ?? 'to-read' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $book['my rating'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="font-medium text-yellow-900 mb-2">{{ __('app.import.important_notes') }}</h3>
            <ul class="list-disc list-inside text-sm text-yellow-800 space-y-1">
                <li>{{ __('app.import.note_duplicates') }}</li>
                <li>{{ __('app.import.note_tag') }}</li>
                <li>{{ __('app.import.note_time') }}</li>
            </ul>
        </div>

        <div class="flex gap-4">
            <form action="{{ route('import.execute') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg">
                    {{ __('app.import.confirm_import') }}
                </button>
            </form>
            <form action="{{ route('import.cancel') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-3 px-6 rounded-lg">
                    {{ __('app.import.cancel') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
