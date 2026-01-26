@extends('layouts.app')

@section('title', __('app.contact.title'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white shadow-sm rounded-lg px-6 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('app.contact.title') }}</h1>
        <p class="text-gray-600 mb-8">{{ __('app.contact.description') }}</p>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->has('error') || $errors->has('turnstile'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-800">{{ $errors->first('error') ?: $errors->first('turnstile') }}</p>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ localeRoute('kontakt.submit') }}" class="space-y-6">
            @csrf

            <!-- Category -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.contact.category_label') }} <span class="text-red-500">*</span>
                </label>
                <select name="category" id="category" required
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('category') border-red-500 @enderror">
                    <option value="">{{ __('app.contact.category_select') }}</option>
                    <option value="support" {{ old('category') === 'support' ? 'selected' : '' }}>
                        📚 {{ __('app.contact.category_support') }}
                    </option>
                    <option value="feature" {{ old('category') === 'feature' ? 'selected' : '' }}>
                        💡 {{ __('app.contact.category_feature') }}
                    </option>
                    <option value="bug" {{ old('category') === 'bug' ? 'selected' : '' }}>
                        🐛 {{ __('app.contact.category_bug') }}
                    </option>
                    <option value="privacy" {{ old('category') === 'privacy' ? 'selected' : '' }}>
                        🔒 {{ __('app.contact.category_privacy') }}
                    </option>
                </select>
                @error('category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.contact.name_label') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="255"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                       placeholder="{{ __('app.contact.name_placeholder') }}">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.contact.email_label') }} <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required maxlength="255"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                       placeholder="{{ __('app.contact.email_placeholder') }}">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Message -->
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.contact.message_label') }} <span class="text-red-500">*</span>
                </label>
                <textarea name="message" id="message" rows="6" required minlength="10" maxlength="5000"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('message') border-red-500 @enderror"
                          placeholder="{{ __('app.contact.message_placeholder') }}">{{ old('message') }}</textarea>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">{{ __('app.contact.message_help') }}</p>
            </div>

            <!-- Turnstile -->
            @if($turnstileEnabled && $turnstileSiteKey)
                <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            @endif

            <!-- Privacy Checkbox -->
            <div>
                <label class="flex items-start">
                    <input type="checkbox" name="privacy" value="1" {{ old('privacy') ? 'checked' : '' }} required
                           class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('privacy') border-red-500 @enderror">
                    <span class="ml-2 text-sm text-gray-700">
                        {!! __('app.contact.privacy_text', ['url' => route('datenschutz')]) !!} <span class="text-red-500">*</span>
                    </span>
                </label>
                @error('privacy')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="/" class="text-gray-600 hover:text-gray-900">← {{ __('app.contact.back_home') }}</a>
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ __('app.contact.submit_button') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
