@extends('layouts.app')

@section('title', __('app.confirm_password.title'))

@section('content')
<div class="flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-6">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">{{ __('app.confirm_password.title') }}</h2>
            <p class="mt-2 text-center text-sm text-gray-600">{{ __('app.confirm_password.intro') }}</p>
        </div>

        <form class="space-y-4" method="POST" action="{{ route('password.confirm.submit') }}">
            @csrf
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">{{ __('app.login.password') }}</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required autofocus maxlength="255" class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">{{ __('app.confirm_password.submit') }}</button>
        </form>
    </div>
</div>
@endsection
