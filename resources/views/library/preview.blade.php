@extends('layouts.app')

@section('title', __('app.library_transfer.preview_title'))

@section('content')
<div class="px-4 max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.library_transfer.preview_title') }}</h1>
        <p class="mt-2 text-gray-600">{{ __('app.library_transfer.preview_description') }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
        <!-- File Info -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ $filename }}</h2>
            @if($preview['user_name'])
                <p class="text-sm text-gray-600">{{ __('app.library_transfer.exported_by') }}: {{ $preview['user_name'] }}</p>
            @endif
            @if($preview['exported_at'])
                <p class="text-sm text-gray-600">{{ __('app.library_transfer.exported_at') }}: {{ \Carbon\Carbon::parse($preview['exported_at'])->format('M d, Y H:i') }}</p>
            @endif
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $preview['statistics']['total_books'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">{{ __('app.library_transfer.stat_books') }}</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $preview['statistics']['total_tags'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">{{ __('app.library_transfer.stat_tags') }}</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $preview['statistics']['total_covers'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">{{ __('app.library_transfer.stat_covers') }}</div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $preview['statistics']['total_challenges'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">{{ __('app.library_transfer.stat_challenges') }}</div>
            </div>
        </div>

        <!-- Books Preview -->
        @if(count($preview['books_preview']) > 0)
        <div class="mb-6">
            <h3 class="font-medium text-gray-900 mb-3">{{ __('app.library_transfer.preview_first_books') }}</h3>
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
                        @foreach($preview['books_preview'] as $book)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $book['title'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $book['author'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($book['status'] === 'read')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">{{ __('app.books.read') }}</span>
                                @elseif($book['status'] === 'currently_reading')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ __('app.books.currently_reading') }}</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">{{ __('app.books.want_to_read') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $book['rating'] ? number_format($book['rating'], 1) : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Duplicate Strategy -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="font-medium text-yellow-900 mb-2">{{ __('app.library_transfer.duplicate_handling') }}</h3>
            <p class="text-sm text-yellow-800 mb-4">{{ __('app.library_transfer.duplicate_help') }}</p>

            <form action="{{ route('library.import.execute') }}" method="POST" id="importForm">
                @csrf
                <div class="space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="duplicate_strategy" value="skip" checked class="mt-1 text-yellow-600 focus:ring-yellow-500">
                        <div>
                            <span class="font-medium text-gray-900">{{ __('app.library_transfer.strategy_skip') }}</span>
                            <p class="text-sm text-gray-600">{{ __('app.library_transfer.strategy_skip_desc') }}</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="duplicate_strategy" value="overwrite" class="mt-1 text-yellow-600 focus:ring-yellow-500">
                        <div>
                            <span class="font-medium text-gray-900">{{ __('app.library_transfer.strategy_overwrite') }}</span>
                            <p class="text-sm text-gray-600">{{ __('app.library_transfer.strategy_overwrite_desc') }}</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="duplicate_strategy" value="keep_both" class="mt-1 text-yellow-600 focus:ring-yellow-500">
                        <div>
                            <span class="font-medium text-gray-900">{{ __('app.library_transfer.strategy_keep_both') }}</span>
                            <p class="text-sm text-gray-600">{{ __('app.library_transfer.strategy_keep_both_desc') }}</p>
                        </div>
                    </label>
                </div>
            </form>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
            <button type="submit" form="importForm" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg">
                {{ __('app.library_transfer.confirm_import') }}
            </button>
            <form action="{{ route('library.import.cancel') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-3 px-6 rounded-lg">
                    {{ __('app.library_transfer.cancel') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
