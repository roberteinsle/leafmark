@extends('layouts.app')

@section('title', __('app.password_reset.forgot_password'))

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
                {{ __('app.password_reset.forgot_password') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('app.password_reset.forgot_password_intro') }}
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

        <form class="mt-8 space-y-6" method="POST" action="{{ localeRoute('password.email') }}">
            @csrf
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">{{ __('app.settings.email') }}</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror"
                           placeholder="{{ __('app.settings.email') }}" value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if(\App\Models\SystemSetting::isTurnstileEnabled())
            <div>
                <div class="cf-turnstile" data-sitekey="{{ \App\Models\SystemSetting::getTurnstileSiteKey() }}"></div>
                @error('cf-turnstile-response')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('app.password_reset.send_reset_link') }}
                </button>
            </div>

            <div class="text-center">
                <a href="{{ localeRoute('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    {{ __('app.password_reset.back_to_login') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
