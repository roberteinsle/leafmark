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

            <!-- Password Change -->
            <div class="border-t pt-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.settings.change_password') }}</h3>

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">{{ __('app.settings.current_password') }}</label>
                        <input type="password" name="current_password" id="current_password" value="{{ old('current_password') }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror"
                               placeholder="{{ __('app.settings.leave_blank_to_keep') }}">
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('app.settings.new_password') }}</label>
                        <input type="password" name="password" id="password" value="{{ old('password') }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                               placeholder="{{ __('app.settings.leave_blank_to_keep') }}">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('app.settings.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" value="{{ old('password_confirmation') }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                               placeholder="{{ __('app.settings.leave_blank_to_keep') }}">
                    </div>

                    <p class="text-sm text-gray-500">
                        {{ __('app.settings.password_help') }}
                    </p>
                </div>
            </div>

            <!-- Amazon Product Advertising API -->
            <div class="border-t pt-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Amazon Product Advertising API</h3>

                <div class="space-y-4">
                    <div>
                        <label for="amazon_access_key" class="block text-sm font-medium text-gray-700">Amazon Access Key</label>
                        <input type="text" name="amazon_access_key" id="amazon_access_key" value="{{ old('amazon_access_key', $user->amazon_access_key) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('amazon_access_key') border-red-500 @enderror"
                               placeholder="Enter your Amazon Access Key">
                        @error('amazon_access_key')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amazon_secret_key" class="block text-sm font-medium text-gray-700">Amazon Secret Key</label>
                        <input type="password" name="amazon_secret_key" id="amazon_secret_key" value="{{ old('amazon_secret_key', $user->amazon_secret_key) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('amazon_secret_key') border-red-500 @enderror"
                               placeholder="Enter your Amazon Secret Key">
                        @error('amazon_secret_key')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amazon_associate_tag" class="block text-sm font-medium text-gray-700">Amazon Associate Tag</label>
                        <input type="text" name="amazon_associate_tag" id="amazon_associate_tag" value="{{ old('amazon_associate_tag', $user->amazon_associate_tag) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('amazon_associate_tag') border-red-500 @enderror"
                               placeholder="Enter your Amazon Associate Tag">
                        @error('amazon_associate_tag')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="text-sm text-gray-500">
                        Optional: Provide your Amazon Product Advertising API credentials for book searches.
                        <a href="https://affiliate-program.amazon.com/assoc_credentials/home" target="_blank" class="text-blue-600 hover:underline">Get API credentials</a>
                    </p>
                </div>
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
