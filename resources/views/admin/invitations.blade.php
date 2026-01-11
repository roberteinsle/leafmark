@extends('layouts.app')

@section('title', __('app.admin.invitation_management'))

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('admin.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            ← {{ __('app.admin.dashboard') }}
        </a>
    </div>

    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('app.admin.invitation_management') }}</h1>
            <p class="mt-2 text-sm text-gray-700">{{ __('app.admin.invitation_management_description') }}</p>
        </div>
    </div>

    <!-- Create Invitation Form -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.admin.create_invitation') }}</h2>

        <form method="POST" action="{{ route('admin.invitations.create') }}" class="max-w-xl">
            @csrf
            <div class="flex gap-3">
                <input type="email" name="email" required
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                       placeholder="{{ __('app.admin.invitation_email') }}">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                    {{ __('app.admin.create_invitation') }}
                </button>
            </div>
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>
    </div>

    <!-- Invitations List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($invitations->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('app.admin.no_invitations') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('app.admin.no_invitations_description') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.invitation_email') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.invitation_status') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.invitation_expires') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.invitation_invited_by') }}
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($invitations as $invitation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $invitation->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($invitation->used_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('app.admin.invitation_used') }}
                                    </span>
                                @elseif($invitation->expires_at->isPast())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ __('app.admin.invitation_expired') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ __('app.admin.invitation_pending') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $invitation->expires_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $invitation->invitedBy->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!$invitation->used_at && !$invitation->expires_at->isPast())
                                        <button
                                            onclick="copyInvitationLink('{{ route('register', ['token' => $invitation->token]) }}')"
                                            class="text-blue-600 hover:text-blue-900">
                                            {{ __('app.admin.copy_invitation_link') }}
                                        </button>
                                        <span class="text-gray-300">|</span>
                                    @endif
                                    <form method="POST" action="{{ route('admin.invitations.delete', $invitation) }}" class="inline"
                                          onsubmit="return confirm('{{ __('app.admin.delete_invitation_confirm') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            {{ __('app.admin.delete_invitation') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($invitations->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $invitations->links() }}
            </div>
            @endif
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function copyInvitationLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        alert('{{ __("app.admin.invitation_link_copied") }}');
    }, function() {
        alert('{{ __("app.admin.copy_failed") }}');
    });
}
</script>
@endpush
