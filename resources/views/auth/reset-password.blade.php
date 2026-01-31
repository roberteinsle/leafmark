@extends('layouts.app')

@section('title', __('app.password_reset.reset_password'))

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
                {{ __('app.password_reset.reset_password') }}
            </h2>
        </div>

        <form class="mt-8 space-y-6" method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ __('app.settings.email') }}</label>
                    <input id="email" name="email" type="email" autocomplete="email" required readonly
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 bg-gray-50 text-gray-900 rounded-md focus:outline-none sm:text-sm"
                           value="{{ $email }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('app.password_reset.new_password') }}</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-500 @enderror"
                           placeholder="{{ __('app.password_reset.new_password') }}">
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('app.password_reset.confirm_password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="{{ __('app.password_reset.confirm_password') }}">
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
                    {{ __('app.password_reset.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
