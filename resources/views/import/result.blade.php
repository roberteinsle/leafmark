@extends('layouts.app')

@section('title', __('app.import.result_title'))

@section('content')
<div class="px-4 max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.import.result_title') }}</h1>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
        @if($importHistory->status === 'completed')
        <div class="flex items-center gap-3 mb-6">
            <div class="flex-shrink-0">
                <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ __('app.import.import_completed') }}</h2>
                <p class="text-gray-600">{{ $importHistory->filename }}</p>
            </div>
        </div>
        @elseif($importHistory->status === 'failed')
        <div class="flex items-center gap-3 mb-6">
            <div class="flex-shrink-0">
                <svg class="h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ __('app.import.import_failed') }}</h2>
                <p class="text-gray-600">{{ $importHistory->filename }}</p>
            </div>
        </div>
        @endif

        <!-- Statistics -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-gray-900">{{ $importHistory->total_rows }}</div>
                <div class="text-sm text-gray-600">{{ __('app.import.total_rows') }}</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-green-600">{{ $importHistory->imported_count }}</div>
                <div class="text-sm text-gray-600">{{ __('app.import.imported') }}</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ $importHistory->skipped_count }}</div>
                <div class="text-sm text-gray-600">{{ __('app.import.skipped') }}</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-red-600">{{ $importHistory->failed_count }}</div>
                <div class="text-sm text-gray-600">{{ __('app.import.failed') }}</div>
            </div>
        </div>

        <!-- Import Tag Info -->
        @if($importHistory->import_tag)
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
            <h3 class="font-medium text-indigo-900 mb-2">{{ __('app.import.import_tag_created') }}</h3>
            <p class="text-sm text-indigo-800 mb-3">{{ __('app.import.import_tag_description') }}</p>
            <div class="flex gap-2">
                <code class="bg-white px-3 py-1 rounded text-indigo-600 font-mono text-sm">{{ $importHistory->import_tag }}</code>
                <a href="{{ route('books.index', ['tag' => $importHistory->import_tag]) }}" 
                   class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                    {{ __('app.import.view_imported_books') }} →
                </a>
            </div>
        </div>
        @endif

        <!-- Errors -->
        @if($importHistory->errors && count($importHistory->errors) > 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <h3 class="font-medium text-red-900 mb-2">{{ __('app.import.errors_occurred') }}</h3>
            <div class="max-h-64 overflow-y-auto">
                <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                    @foreach($importHistory->errors as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex gap-4">
            <a href="{{ route('books.index') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg text-center">
                {{ __('app.import.view_library') }}
            </a>
            <a href="{{ route('import.index') }}" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-3 px-6 rounded-lg text-center">
                {{ __('app.import.import_another') }}
            </a>
        </div>
    </div>
</div>
@endsection
