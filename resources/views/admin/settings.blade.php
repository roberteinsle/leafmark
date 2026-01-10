@extends('layouts.app')

@section('title', __('app.admin.system_settings'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
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
                    <option value="invitation" {{ $registrationMode === 'invitation' ? 'selected' : '' }}>
                        {{ __('app.admin.mode_invitation') }}
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

    <!-- SMTP Settings -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('app.admin.smtp_settings') }}</h2>

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="section" value="smtp">

            <!-- Enable SMTP -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="smtp_enabled" value="1" {{ $smtpEnabled ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">{{ __('app.admin.smtp_enabled') }}</span>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- SMTP Host -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.smtp_host') }}
                    </label>
                    <input type="text" name="smtp_host" value="{{ $smtpHost }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           placeholder="smtp.gmail.com">
                </div>

                <!-- SMTP Port -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.smtp_port') }}
                    </label>
                    <input type="number" name="smtp_port" value="{{ $smtpPort }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           placeholder="587">
                </div>

                <!-- SMTP Encryption -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.smtp_encryption') }}
                    </label>
                    <select name="smtp_encryption" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="tls" {{ $smtpEncryption === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ $smtpEncryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="" {{ $smtpEncryption === '' ? 'selected' : '' }}>{{ __('app.admin.none') }}</option>
                    </select>
                </div>

                <!-- SMTP Username -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.smtp_username') }}
                    </label>
                    <input type="text" name="smtp_username" value="{{ $smtpUsername }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           autocomplete="off">
                </div>

                <!-- SMTP Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.smtp_password') }}
                    </label>
                    <input type="password" name="smtp_password" value="{{ $smtpPassword }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           autocomplete="new-password">
                </div>

                <!-- From Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.smtp_from_address') }}
                    </label>
                    <input type="email" name="smtp_from_address" value="{{ $smtpFromAddress }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           placeholder="noreply@leafmark.app">
                </div>

                <!-- From Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.smtp_from_name') }}
                    </label>
                    <input type="text" name="smtp_from_name" value="{{ $smtpFromName }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           placeholder="Leafmark">
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                    {{ __('app.admin.save_settings') }}
                </button>
            </div>
        </form>

        <!-- Test Email Form (separate form outside main form) -->
        <form method="POST" action="{{ route('admin.settings.test-email') }}" class="mt-4 border-t pt-4">
            @csrf
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.test_email_recipient') }}
                    </label>
                    <input type="email" name="test_email" required
                           value="{{ auth()->user()->email }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           placeholder="email@example.com">
                    @error('test_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    {{ __('app.admin.send_test_email') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Turnstile Settings -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('app.admin.turnstile_settings') }}</h2>

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="section" value="turnstile">

            <!-- Enable Turnstile -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="turnstile_enabled" value="1" {{ $turnstileEnabled ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">{{ __('app.admin.turnstile_enabled') }}</span>
                </label>
                <p class="mt-1 text-sm text-gray-500">{{ __('app.admin.turnstile_help') }}</p>
            </div>

            <!-- Site Key -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.admin.turnstile_site_key') }}
                </label>
                <input type="text" name="turnstile_site_key" value="{{ $turnstileSiteKey }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                       placeholder="0x4AAAAAACLtqg1wek2WGtof">
            </div>

            <!-- Secret Key -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.admin.turnstile_secret_key') }}
                </label>
                <input type="password" name="turnstile_secret_key" value="{{ $turnstileSecretKey }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                       autocomplete="new-password">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                    {{ __('app.admin.save_settings') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Invitations -->
    <div id="invitations" class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('app.admin.invitation_management') }}</h2>

        <!-- Create Invitation Form -->
        <form method="POST" action="{{ route('admin.invitations.create') }}" class="mb-6">
            @csrf
            <div class="flex gap-2">
                <input type="email" name="email" required
                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                       placeholder="{{ __('app.admin.invitation_email') }}">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                    {{ __('app.admin.create_invitation') }}
                </button>
            </div>
        </form>

        <!-- Invitations List -->
        @if($invitations->isEmpty())
        <p class="text-gray-500 text-center py-4">No invitations yet.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.admin.invitation_email') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.admin.invitation_status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.admin.invitation_expires') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.admin.invitation_invited_by') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invitations as $invitation)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $invitation->email }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($invitation->used_at)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                {{ __('app.admin.invitation_used') }}
                            </span>
                            @elseif($invitation->expires_at->isPast())
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                {{ __('app.admin.invitation_expired') }}
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ __('app.admin.invitation_pending') }}
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $invitation->expires_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $invitation->invitedBy->name }}</td>
                        <td class="px-4 py-3 text-sm text-right">
                            @if(!$invitation->used_at && !$invitation->expires_at->isPast())
                            <button onclick="copyInvitationLink('{{ route('register', ['token' => $invitation->token]) }}')"
                                    class="text-indigo-600 hover:text-indigo-900 mr-3">
                                {{ __('app.admin.copy_invitation_link') }}
                            </button>
                            @endif

                            <form method="POST" action="{{ route('admin.invitations.delete', $invitation) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    {{ __('app.admin.delete_invitation') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $invitations->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function copyInvitationLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        alert('Invitation link copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
@endsection
