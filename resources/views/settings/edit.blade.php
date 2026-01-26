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

    <div class="bg-white rounded-lg shadow-sm" x-data="{ activeTab: 'account' }">
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
                <button @click="activeTab = 'email'"
                        :class="activeTab === 'email' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    {{ __('app.settings.email_events') }}
                </button>
            </nav>
        </div>

        <form action="{{ localeRoute('settings.update') }}" method="POST" class="p-6">
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

            <div class="flex justify-end gap-3 pt-4" x-show="activeTab === 'account' || activeTab === 'security'">
                <a href="{{ localeRoute('books.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    {{ __('app.settings.cancel') }}
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">
                    {{ __('app.settings.save_changes') }}
                </button>
            </div>
        </form>

        <!-- Email Events Tab (Read-only) -->
        <div x-show="activeTab === 'email'" class="p-6" style="display: none;">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('app.settings.recent_email_events') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('app.settings.email_events_description') }}</p>
            </div>

            @if($recentEmailEvents->isEmpty())
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">{{ __('app.settings.no_email_events') }}</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentEmailEvents as $event)
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $event->status === 'sent' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $event->status === 'sent' ? __('app.settings.email_sent') : __('app.settings.email_failed') }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $event->type }}</span>
                                    </div>
                                    <p class="mt-1 text-sm font-medium text-gray-900">{{ $event->subject }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('app.settings.to') }}: {{ $event->recipient }}</p>
                                    @if($event->status === 'failed' && $event->error_message)
                                        <p class="mt-2 text-sm text-red-600">{{ __('app.settings.error') }}: {{ Str::limit($event->error_message, 100) }}</p>
                                    @endif
                                </div>
                                <div class="ml-4 flex-shrink-0 text-right">
                                    <p class="text-xs text-gray-500">{{ $event->created_at->diffForHumans() }}</p>
                                    <p class="text-xs text-gray-400">{{ $event->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(auth()->user()->is_admin)
                    <div class="mt-4 text-center">
                        <a href="{{ localeRoute('admin.email-logs') }}" class="text-sm text-blue-600 hover:text-blue-800">
                            {{ __('app.settings.view_all_email_logs') }} →
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
