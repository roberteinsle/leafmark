@extends('layouts.app')

@section('title', __('app.two_factor.challenge_title'))

@section('content')
<div class="flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-6" x-data="{ recovery: {{ $errors->has('recovery_code') ? 'true' : 'false' }} }">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">{{ __('app.two_factor.challenge_title') }}</h2>
            <p class="mt-2 text-center text-sm text-gray-600" x-show="!recovery">{{ __('app.two_factor.challenge_intro') }}</p>
            <p class="mt-2 text-center text-sm text-gray-600" x-show="recovery" x-cloak>{{ __('app.two_factor.recovery_intro') }}</p>
        </div>

        <form class="space-y-4" method="POST" action="{{ route('two-factor.challenge.submit') }}">
            @csrf
            <div x-show="!recovery">
                <label for="code" class="block text-sm font-medium text-gray-700">{{ __('app.two_factor.code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="16" autofocus
                       class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" x-bind:disabled="recovery">
            </div>
            <div x-show="recovery" x-cloak>
                <label for="recovery_code" class="block text-sm font-medium text-gray-700">{{ __('app.two_factor.recovery_code') }}</label>
                <input id="recovery_code" name="recovery_code" type="text" autocomplete="off" maxlength="32"
                       class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" x-bind:disabled="!recovery">
            </div>
            @error('code')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">{{ __('app.two_factor.verify') }}</button>
        </form>

        <p class="text-center text-sm">
            <button type="button" class="font-medium text-indigo-600 hover:text-indigo-500" @click="recovery = !recovery">
                <span x-show="!recovery">{{ __('app.two_factor.use_recovery_code') }}</span>
                <span x-show="recovery" x-cloak>{{ __('app.two_factor.use_code') }}</span>
            </button>
        </p>
    </div>
</div>
@endsection
