@extends('layouts.app')

@section('title', __('app.nav.family'))

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('app.family.title') }}</h1>
            <p class="mt-2 text-sm text-gray-700">{{ __('app.family.description') }}</p>
        </div>
    </div>

    @if($family)
        <!-- Family Info -->
        <div class="mt-8 bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $family->name }}</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('app.family.member_count', ['count' => $family->member_count]) }}
                        </p>
                    </div>
                    @if($family->isOwner($user))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ __('app.family.owner') }}
                        </span>
                    @endif
                </div>

                <!-- Join Code Section (Owner Only) -->
                @if($family->isOwner($user))
                    <div class="bg-gray-50 rounded-lg p-6 mb-6" x-data="{ copied: false }">
                        <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('app.family.join_code') }}</h3>
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <code class="flex-1 px-4 py-3 bg-white border border-gray-300 rounded-lg text-2xl font-mono tracking-widest text-gray-900">
                                        {{ $family->join_code }}
                                    </code>
                                    <button
                                        @click="navigator.clipboard.writeText('{{ $family->join_code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                    >
                                        <span x-show="!copied">{{ __('app.family.copy_code') }}</span>
                                        <span x-show="copied" x-cloak>✓ {{ __('app.family.copied') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-gray-600">{{ __('app.family.share_code_info') }}</p>

                        <div class="mt-4">
                            <form action="{{ localeRoute('family.regenerate-code') }}" method="POST" onsubmit="return confirm('{{ __('app.family.regenerate_confirm') }}')">
                                @csrf
                                <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                                    {{ __('app.family.regenerate_code') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Members List -->
                <div>
                    <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('app.family.members') }}</h3>
                    <div class="space-y-3">
                        @foreach($family->members as $member)
                            <div class="flex items-center justify-between py-3 px-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-blue-600 font-semibold text-lg">
                                            {{ substr($member->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $member->email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-600">
                                        {{ __('app.family.books_count', ['count' => $member->books()->count()]) }}
                                    </span>
                                    @if($family->isOwner($member))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ __('app.family.owner') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-between">
                    <form action="{{ localeRoute('family.leave') }}" method="POST" onsubmit="return confirm('{{ __('app.family.leave_confirm') }}')">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                            {{ __('app.family.leave_family_button') }}
                        </button>
                    </form>

                    @if($family->isOwner($user))
                        <form action="{{ localeRoute('family.destroy') }}" method="POST" onsubmit="return confirm('{{ __('app.family.delete_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                {{ __('app.family.delete_family') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @else
        <!-- No Family -->
        <div class="mt-8 bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('app.family.no_family_title') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('app.family.no_family_description') }}</p>

                <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ localeRoute('family.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        {{ __('app.family.create_family') }}
                    </a>
                    <a href="{{ localeRoute('family.join') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        {{ __('app.family.join_existing') }}
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
