@extends('layouts.app')

@section('title', __('app.settings.title'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.settings.title') }}</h1>
        <p class="mt-2 text-gray-600">{{ __('app.settings.manage_preferences') }}</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('app.settings.name') }}</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('app.settings.email') }}</label>
                <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="preferred_language" class="block text-sm font-medium text-gray-700">{{ __('app.settings.preferred_language') }}</label>
                <select name="preferred_language" id="preferred_language" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('preferred_language') border-red-500 @enderror">
                    @foreach($availableLanguages as $code => $name)
                        <option value="{{ $code }}" {{ old('preferred_language', $user->preferred_language ?? 'en') === $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500">{{ __('app.settings.language_help') }}</p>
                @error('preferred_language')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="google_books_api_key" class="block text-sm font-medium text-gray-700">{{ __('app.settings.google_books_api_key') }}</label>
                <input type="text" name="google_books_api_key" id="google_books_api_key" value="{{ old('google_books_api_key', $user->google_books_api_key) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('google_books_api_key') border-red-500 @enderror"
                       placeholder="{{ __('app.settings.api_key_placeholder') }}">
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('app.settings.api_key_help') }}
                    <a href="https://developers.google.com/books/docs/v1/using#APIKey" target="_blank" class="text-blue-600 hover:underline">{{ __('app.settings.get_api_key') }}</a>
                </p>
                @error('google_books_api_key')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('books.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    {{ __('app.settings.cancel') }}
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">
                    {{ __('app.settings.save_changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
