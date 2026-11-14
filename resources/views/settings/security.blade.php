@extends('layouts.app')

@section('title', __('app.security.title'))

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 space-y-8">
    <div>
        <a href="{{ route('settings.edit', ['tab' => 'security']) }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; {{ __('app.settings.title') }}</a>
        <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ __('app.security.title') }}</h1>
        <p class="mt-2 text-gray-600">{{ __('app.security.intro') }}</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('status') === 'passkey-deleted')
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-green-800">{{ __('app.passkeys.deleted') }}</p>
        </div>
    @endif

    {{-- Passkeys (Standard Abschnitt 11, Rang 1) --}}
    <section class="bg-white rounded-lg shadow-sm p-6 space-y-4"
             x-data="passkeyRegister(@js([
                 'unsupported' => __('app.passkeys.unsupported'),
                 'failed' => __('app.passkeys.register_failed'),
                 'exists' => __('app.passkeys.exists'),
                 'defaultName' => __('app.passkeys.default_name'),
             ]))">
        @if($offerPasskey)
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                <p class="font-medium text-indigo-900">{{ __('app.passkeys.offer_title') }}</p>
                <p class="text-sm text-indigo-800 mt-1">{{ __('app.passkeys.offer_text') }}</p>
                <a href="{{ route('books.index') }}" class="inline-block mt-2 text-sm text-indigo-700 underline">{{ __('app.passkeys.offer_later') }}</a>
            </div>
        @endif

        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ __('app.passkeys.title') }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ __('app.passkeys.intro') }}</p>
        </div>

        @if($passkeys->isNotEmpty())
            <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                @foreach($passkeys as $passkey)
                    <li class="flex items-center justify-between gap-4 p-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ $passkey->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ __('app.passkeys.added', ['date' => $passkey->created_at->isoFormat('LL')]) }}
                                @if($passkey->last_used_at)
                                    · {{ __('app.passkeys.last_used', ['date' => $passkey->last_used_at->diffForHumans()]) }}
                                @endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('passkey.destroy', $passkey) }}" onsubmit="return confirm(@js(__('app.passkeys.delete_confirm')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">{{ __('app.passkeys.delete') }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">{{ __('app.passkeys.none') }}</p>
        @endif

        <div x-show="supported" class="flex flex-col sm:flex-row gap-2">
            <label for="passkey-name" class="sr-only">{{ __('app.passkeys.name_label') }}</label>
            <input id="passkey-name" type="text" x-model="name" maxlength="100"
                   placeholder="{{ __('app.passkeys.name_placeholder') }}"
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            <button type="button" @click="register()" :disabled="busy"
                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 disabled:opacity-60 whitespace-nowrap">
                {{ __('app.passkeys.add') }}
            </button>
        </div>
        <p x-show="!supported" x-cloak class="text-sm text-gray-500">{{ __('app.passkeys.unsupported') }}</p>
        <p x-show="error" x-text="error" x-cloak class="text-sm text-red-600" role="alert"></p>
    </section>

    {{-- Zwei-Faktor für den Passwort-Login (Standard Abschnitt 11, Rang 3) --}}
    <section class="bg-white rounded-lg shadow-sm p-6 space-y-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ __('app.two_factor.title') }}</h2>
            <p class="text-sm text-gray-600 mt-1">{{ __('app.two_factor.intro') }}</p>
        </div>

        @if($recoveryCodes)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="font-medium text-yellow-900">{{ __('app.two_factor.recovery_codes_title') }}</p>
                <p class="text-sm text-yellow-800 mt-1">{{ __('app.two_factor.recovery_codes_help') }}</p>
                <ul class="mt-3 grid grid-cols-2 gap-2 font-mono text-sm">
                    @foreach($recoveryCodes as $code)
                        <li class="bg-white border border-yellow-200 rounded px-2 py-1 select-all">{{ $code }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($user->hasTwoFactorEnabled())
            <p class="text-sm text-green-700 font-medium">
                {{ __('app.two_factor.status_enabled', ['date' => $user->two_factor_confirmed_at->isoFormat('LL')]) }}
                · {{ trans_choice('app.two_factor.recovery_codes_left', count($user->two_factor_recovery_codes ?? []), ['count' => count($user->two_factor_recovery_codes ?? [])]) }}
            </p>
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('settings.two-factor.recovery-codes') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">{{ __('app.two_factor.regenerate_codes') }}</button>
                </form>
                <form method="POST" action="{{ route('settings.two-factor.disable') }}" onsubmit="return confirm(@js(__('app.two_factor.disable_confirm')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 border border-red-300 rounded-md text-red-700 hover:bg-red-50">{{ __('app.two_factor.disable') }}</button>
                </form>
            </div>
        @elseif($setupSecret)
            @include('settings.partials.two-factor-qr', ['qrCode' => $qrCode, 'secret' => $setupSecret])
            <form method="POST" action="{{ route('settings.two-factor.confirm') }}" class="flex flex-col sm:flex-row gap-2 sm:items-start">
                @csrf
                <div class="flex-1">
                    <label for="code" class="sr-only">{{ __('app.two_factor.code') }}</label>
                    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="16" required
                           placeholder="{{ __('app.two_factor.code') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">{{ __('app.two_factor.confirm') }}</button>
            </form>
        @else
            <p class="text-sm text-gray-500">{{ __('app.two_factor.status_disabled') }}</p>
            <form method="POST" action="{{ route('settings.two-factor.start') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">{{ __('app.two_factor.enable') }}</button>
            </form>
        @endif
    </section>
</div>
@endsection
