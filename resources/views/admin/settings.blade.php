@extends('layouts.app')

@section('title', __('app.admin.system_settings'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Breadcrumb Navigation -->
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    {{ __('app.admin.dashboard') }}
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('app.admin.system_settings') }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.admin.system_settings') }}</h1>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Registration Settings -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('app.admin.registration_settings') }}</h2>

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PATCH')

            <!-- Enable/Disable Registration -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="registration_enabled" value="1" {{ $registrationEnabled ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">{{ __('app.admin.registration_enabled') }}</span>
                </label>
            </div>

            <!-- Registration Mode -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.admin.registration_mode') }}
                </label>
                <select name="registration_mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="open" {{ $registrationMode === 'open' ? 'selected' : '' }}>
                        {{ __('app.admin.mode_open') }}
                    </option>
                    <option value="domain" {{ $registrationMode === 'domain' ? 'selected' : '' }}>
                        {{ __('app.admin.mode_domain') }}
                    </option>
                    <option value="code" {{ $registrationMode === 'code' ? 'selected' : '' }}>
                        {{ __('app.admin.mode_code') }}
                    </option>
                </select>
            </div>

            <!-- Allowed Email Domains -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.admin.allowed_domains') }}
                </label>
                <input type="text" name="allowed_email_domains" value="{{ $allowedDomains }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                       placeholder="einsle.com, example.org">
                <p class="mt-1 text-sm text-gray-500">{{ __('app.admin.allowed_domains_help') }}</p>
            </div>

            <!-- Registration Code -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.admin.registration_code') }}
                </label>
                <input type="text" name="registration_code" value="{{ $registrationCode }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                       placeholder="LEAFMARK2026">
                <p class="mt-1 text-sm text-gray-500">{{ __('app.admin.registration_code_help') }}</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                    {{ __('app.admin.save_settings') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
