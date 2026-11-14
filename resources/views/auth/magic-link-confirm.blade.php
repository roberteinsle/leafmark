@extends('layouts.app')

@section('title', __('app.magic_link.confirm_title'))

@section('content')
<div class="flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-6 text-center">
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900">{{ __('app.magic_link.confirm_title') }}</h2>
        <p class="text-sm text-gray-600">{{ __('app.magic_link.confirm_intro') }}</p>

        <form method="POST" action="{{ route('login.magic.consume') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('app.magic_link.confirm_button') }}
            </button>
        </form>
    </div>
</div>
@endsection
