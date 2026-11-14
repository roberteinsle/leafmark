@extends('layouts.app')

@section('title', __('app.login.title'))

@section('content')
<div class="flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                {{ __('app.login.title') }}
            </h2>
        </div>

        {{-- Reihenfolge nach Standard Abschnitt 11: Passkey → Magic Link → Passwort (+2FA) --}}
        <div class="space-y-3"
             x-data="passkeyLogin(@js([
                 'unsupported' => __('app.passkeys.unsupported'),
                 'failed' => __('app.passkeys.login_failed'),
                 'exists' => __('app.passkeys.login_failed'),
             ]))">
            <button type="button" x-show="supported" x-cloak @click="login()" :disabled="busy"
                    class="w-full flex justify-center items-center gap-2 py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                {{ __('app.login.with_passkey') }}
            </button>
            <p x-show="error" x-text="error" x-cloak class="text-sm text-red-600" role="alert"></p>

            @if (\App\Models\SystemSetting::isSmtpEnabled())
                <a href="{{ route('login.magic') }}"
                   class="w-full flex justify-center py-2 px-4 border border-indigo-600 text-sm font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('app.login.with_magic_link') }}
                </a>
            @endif

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-gray-300"></div></div>
                <div class="relative flex justify-center text-sm"><span class="bg-gray-50 px-2 text-gray-500">{{ __('app.login.or_password') }}</span></div>
            </div>
        </div>

        <form class="space-y-6" method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">{{ __('app.login.email') }}</label>
                    <input id="email" name="email" type="email" autocomplete="email webauthn" required maxlength="255"
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror"
                           placeholder="{{ __('app.login.email') }}" value="{{ old('email') }}">
                </div>
                <div>
                    <label for="password" class="sr-only">{{ __('app.login.password') }}</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror"
                           placeholder="{{ __('app.login.password') }}">
                </div>
            </div>
            @error('email')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('password')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="remember" class="ml-2 block text-sm text-gray-900">
                    {{ __('app.login.remember') }}
                </label>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('app.login.submit') }}
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    {{ __('app.login.no_account') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
