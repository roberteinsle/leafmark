@extends('layouts.app')

@section('title', __('app.family.join_existing'))

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('family.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                ← {{ __('app.family.back_to_family') }}
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-6">
                <h1 class="text-2xl font-semibold text-gray-900 mb-2">{{ __('app.family.join_existing') }}</h1>
                <p class="text-sm text-gray-600 mb-6">{{ __('app.family.join_description') }}</p>

                <form action="{{ route('family.join.submit') }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label for="join_code" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('app.family.enter_join_code') }}
                        </label>
                        <input
                            type="text"
                            name="join_code"
                            id="join_code"
                            value="{{ old('join_code') }}"
                            required
                            maxlength="8"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-2xl font-mono tracking-widest uppercase focus:ring-blue-500 focus:border-blue-500 @error('join_code') border-red-500 @enderror"
                            placeholder="XXXXXXXX"
                            style="text-transform: uppercase"
                        >
                        @error('join_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">{{ __('app.family.join_code_help') }}</p>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">{{ __('app.family.join_note') }}</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>{{ __('app.family.join_note_text') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('family.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            {{ __('app.forms.cancel') }}
                        </a>
                        <button
                            type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            {{ __('app.family.join_family_button') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
