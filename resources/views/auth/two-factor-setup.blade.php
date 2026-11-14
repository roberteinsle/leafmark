@extends('layouts.app')

@section('title', __('app.two_factor.setup_title'))

@section('content')
<div class="flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-6">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">{{ __('app.two_factor.setup_title') }}</h2>
            <p class="mt-2 text-center text-sm text-gray-600">{{ __('app.two_factor.setup_required_intro') }}</p>
        </div>

        @include('settings.partials.two-factor-qr', ['qrCode' => $qrCode, 'secret' => $secret])

        <form class="space-y-4" method="POST" action="{{ route('two-factor.setup.submit') }}">
            @csrf
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">{{ __('app.two_factor.code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="16" required autofocus class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                @error('code')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">{{ __('app.two_factor.enable_and_login') }}</button>
        </form>

        <p class="text-center text-sm text-gray-600">
            {{ __('app.two_factor.setup_alternative') }}
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ __('app.magic_link.back_to_login') }}</a>
        </p>
    </div>
</div>
@endsection
