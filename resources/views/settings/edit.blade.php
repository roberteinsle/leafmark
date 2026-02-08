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

    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-blue-800">{{ session('info') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm" x-data="{ activeTab: '{{ request('tab', 'account') }}' }">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'account'"
                        :class="activeTab === 'account' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    {{ __('app.settings.account') }}
                </button>
                <button @click="activeTab = 'security'"
                        :class="activeTab === 'security' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    {{ __('app.settings.security') }}
                </button>
                <button @click="activeTab = 'data'"
                        :class="activeTab === 'data' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    {{ __('app.settings.data_management') }}
                </button>
            </nav>
        </div>

        <!-- Account & Security form -->
        <form action="{{ route('settings.update') }}" method="POST" class="p-6" x-show="activeTab !== 'data'">
            @csrf
            @method('PATCH')

            <!-- Account Tab -->
            <div x-show="activeTab === 'account'" class="space-y-6">
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
                            <option value="{{ $code }}" {{ old('preferred_language', $user->preferred_language) === $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('preferred_language')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">{{ __('app.settings.preferred_language_help') }}</p>
                </div>

            </div>

            <!-- Security Tab -->
            <div x-show="activeTab === 'security'" class="space-y-6" style="display: none;">
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

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('books.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    {{ __('app.settings.cancel') }}
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">
                    {{ __('app.settings.save_changes') }}
                </button>
            </div>
        </form>

        <!-- Data Management Tab (outside form) -->
        <div x-show="activeTab === 'data'" class="p-6 space-y-8" style="display: none;">

            <!-- Export Section -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.library_transfer.export_title') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('app.library_transfer.export_description') }}</p>

                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">{{ __('app.library_transfer.export_includes') }}</p>
                    <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                        <li>{{ __('app.library_transfer.export_includes_books') }}</li>
                        <li>{{ __('app.library_transfer.export_includes_covers') }}</li>
                        <li>{{ __('app.library_transfer.export_includes_tags') }}</li>
                        <li>{{ __('app.library_transfer.export_includes_progress') }}</li>
                        <li>{{ __('app.library_transfer.export_includes_challenges') }}</li>
                    </ul>
                </div>

                <a href="{{ route('library.export') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('app.library_transfer.export_button') }}
                </a>
            </div>

            <hr class="border-gray-200">

            <!-- Leafmark ZIP Import Section -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.library_transfer.import_title') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('app.library_transfer.import_description') }}</p>

                <a href="{{ route('library.import') }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-md hover:bg-green-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    {{ __('app.library_transfer.import_button') }}
                </a>
            </div>

            <hr class="border-gray-200">

            <!-- Goodreads CSV Import Section -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.library_transfer.goodreads_title') }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('app.library_transfer.goodreads_description') }}</p>

                <a href="{{ route('import.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-orange-500 text-white font-medium rounded-md hover:bg-orange-600">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('app.library_transfer.goodreads_button') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
