@extends('layouts.app')

@section('title', __('app.magic_link.title'))

@section('content')
<div class="flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-6">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">{{ __('app.magic_link.title') }}</h2>
            <p class="mt-2 text-center text-sm text-gray-600">{{ __('app.magic_link.intro') }}</p>
        </div>

        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('status') }}</div>
        @endif

        <form class="space-y-4" method="POST" action="{{ route('login.magic.send') }}">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('app.login.email') }}</label>
                <input id="email" name="email" type="email" autocomplete="email" required maxlength="255"
                       class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-500 @enderror"
                       value="{{ old('email') }}">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('app.magic_link.send_button') }}
            </button>
        </form>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ __('app.magic_link.back_to_login') }}</a>
        </p>
    </div>
</div>
@endsection
