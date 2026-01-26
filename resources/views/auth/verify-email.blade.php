@extends('layouts.app')

@section('title', __('app.email_verification.verify_email'))

@if(\App\Models\SystemSetting::isTurnstileEnabled())
@section('head')
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endsection
@endif

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                {{ __('app.email_verification.verify_email') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('app.email_verification.check_email') }}
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ __('app.email_verification.resend_title') }}
                </h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>{{ __('app.email_verification.resend_intro') }}</p>
                </div>
                <form class="mt-5" method="POST" action="{{ localeRoute('verification.resend') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">{{ __('app.settings.email') }}</label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                   class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-500 @enderror"
                                   placeholder="{{ __('app.settings.email') }}" value="{{ session('email', old('email')) }}">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(\App\Models\SystemSetting::isTurnstileEnabled())
                        <div>
                            <div class="cf-turnstile" data-sitekey="{{ \App\Models\SystemSetting::getTurnstileSiteKey() }}"></div>
                            @error('cf-turnstile-response')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('app.email_verification.resend_button') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ localeRoute('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                {{ __('app.password_reset.back_to_login') }}
            </a>
        </div>
    </div>
</div>
@endsection
