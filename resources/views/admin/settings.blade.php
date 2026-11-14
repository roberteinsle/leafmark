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

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6" role="alert">
        <ul class="text-red-800 text-sm list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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
                    {{-- Ein nicht angehakter Checkbox-Wert wird nicht gesendet; das Hidden-Feld liefert dann "0" --}}
                    <input type="hidden" name="registration_enabled" value="0">
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
                       placeholder="example.com, example.org">
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

    @php($input = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500')

    <!-- E-Mail (SMTP) -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-1">{{ __('app.admin.mail_title') }}</h2>
        <p class="text-sm text-gray-600 mb-4">{{ __('app.admin.mail_intro') }}</p>

        <form method="POST" action="{{ route('admin.settings.mail') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <label class="flex items-center">
                <input type="hidden" name="smtp_enabled" value="0">
                <input type="checkbox" name="smtp_enabled" value="1" {{ old('smtp_enabled', $smtpEnabled) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                <span class="ml-2 text-sm text-gray-700">{{ __('app.admin.mail_enabled') }}</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="smtp_host" class="block text-sm font-medium text-gray-700">{{ __('app.admin.mail_host') }}</label>
                    <input id="smtp_host" type="text" name="smtp_host" value="{{ old('smtp_host', $smtp['host']) }}" maxlength="255" class="{{ $input }}" placeholder="smtp.example.com">
                </div>
                <div>
                    <label for="smtp_port" class="block text-sm font-medium text-gray-700">{{ __('app.admin.mail_port') }}</label>
                    <input id="smtp_port" type="number" name="smtp_port" value="{{ old('smtp_port', $smtp['port']) }}" min="1" max="65535" class="{{ $input }}">
                </div>
            </div>

            <div>
                <label for="smtp_encryption" class="block text-sm font-medium text-gray-700">{{ __('app.admin.mail_encryption') }}</label>
                <select id="smtp_encryption" name="smtp_encryption" class="{{ $input }}">
                    @foreach(['tls' => __('app.admin.mail_encryption_tls'), 'ssl' => __('app.admin.mail_encryption_ssl'), 'none' => __('app.admin.mail_encryption_none')] as $value => $label)
                        <option value="{{ $value }}" {{ old('smtp_encryption', $smtp['encryption']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="smtp_username" class="block text-sm font-medium text-gray-700">{{ __('app.admin.mail_username') }}</label>
                    <input id="smtp_username" type="text" name="smtp_username" value="{{ old('smtp_username', $smtp['username']) }}" maxlength="255" autocomplete="off" class="{{ $input }}">
                </div>
                <div>
                    <label for="smtp_password" class="block text-sm font-medium text-gray-700">{{ __('app.admin.mail_password') }}</label>
                    <input id="smtp_password" type="password" name="smtp_password" maxlength="255" autocomplete="new-password" class="{{ $input }}"
                           placeholder="{{ $hasSmtpPassword ? __('app.admin.secret_keep') : '' }}">
                    @if($hasSmtpPassword)
                        <label class="mt-1 flex items-center text-xs text-gray-600">
                            <input type="checkbox" name="smtp_password_clear" value="1" class="rounded border-gray-300 mr-1">{{ __('app.admin.secret_clear') }}
                        </label>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="smtp_from_address" class="block text-sm font-medium text-gray-700">{{ __('app.admin.mail_from_address') }}</label>
                    <input id="smtp_from_address" type="email" name="smtp_from_address" value="{{ old('smtp_from_address', $smtp['from_address']) }}" maxlength="255" class="{{ $input }}" placeholder="books@example.com">
                </div>
                <div>
                    <label for="smtp_from_name" class="block text-sm font-medium text-gray-700">{{ __('app.admin.mail_from_name') }}</label>
                    <input id="smtp_from_name" type="text" name="smtp_from_name" value="{{ old('smtp_from_name', $smtp['from_name']) }}" maxlength="255" class="{{ $input }}">
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                <button type="submit" form="mail-test-form" class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg">{{ __('app.admin.mail_test_button') }}</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">{{ __('app.admin.save_settings') }}</button>
            </div>
        </form>
        <form id="mail-test-form" method="POST" action="{{ route('admin.settings.mail.test') }}">@csrf</form>
    </div>

    <!-- API-Schlüssel der Buchquellen -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-1">{{ __('app.admin.api_keys_title') }}</h2>
        <p class="text-sm text-gray-600 mb-4">{{ __('app.admin.api_keys_intro') }}</p>

        <form method="POST" action="{{ route('admin.settings.api-keys') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            @foreach([
                'google_books_api_key' => ['label' => 'Google Books', 'set' => $hasGoogleKey, 'help' => __('app.admin.google_key_help')],
                'bigbook_api_key' => ['label' => 'Big Book API', 'set' => $hasBigBookKey, 'help' => __('app.admin.bigbook_key_help')],
            ] as $key => $field)
                <div>
                    <label for="{{ $key }}" class="block text-sm font-medium text-gray-700">{{ $field['label'] }}</label>
                    <input id="{{ $key }}" type="password" name="{{ $key }}" maxlength="255" autocomplete="off" class="{{ $input }}"
                           placeholder="{{ $field['set'] ? __('app.admin.secret_keep') : __('app.admin.secret_not_set') }}">
                    <p class="mt-1 text-xs text-gray-500">{{ $field['help'] }}</p>
                    @if($key === 'google_books_api_key' && $googleKeyFromEnv)
                        <p class="mt-1 text-xs text-gray-500">{{ __('app.admin.google_key_from_env') }}</p>
                    @endif
                    @if($field['set'])
                        <label class="mt-1 flex items-center text-xs text-gray-600">
                            <input type="checkbox" name="{{ $key }}_clear" value="1" class="rounded border-gray-300 mr-1">{{ __('app.admin.secret_clear') }}
                        </label>
                    @endif
                </div>
            @endforeach

            <div class="flex flex-wrap justify-end gap-3">
                <button type="submit" form="google-test-form" class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg">{{ __('app.admin.google_test_button') }}</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">{{ __('app.admin.save_settings') }}</button>
            </div>
        </form>
        <form id="google-test-form" method="POST" action="{{ route('admin.settings.api-keys.test-google') }}">@csrf</form>
    </div>
</div>
@endsection
