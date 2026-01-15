@extends('layouts.app')

@section('title', __('app.changelog.title'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ __('app.changelog.title') }}</h1>
        <p class="text-lg text-gray-600">{{ __('app.changelog.subtitle') }}</p>
    </div>

    <div class="space-y-8">
        <!-- January 2026 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                <h2 class="text-2xl font-bold text-white">{{ __('app.changelog.january_2026') }}</h2>
            </div>
            <div class="p-6 space-y-6">

                <!-- January 15, 2026 -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('app.changelog.jan_15_title') }}</h3>
                        <span class="text-sm text-gray-500">{{ __('app.changelog.jan_15_date') }}</span>
                    </div>
                    <div class="space-y-3 text-gray-700">
                        <div class="flex items-start">
                            <span class="text-green-500 mr-2 mt-1">✨</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_15_contact_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_15_contact_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-green-500 mr-2 mt-1">📚</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_15_bigbook_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_15_bigbook_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-green-500 mr-2 mt-1">📧</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_15_email_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_15_email_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-1">🔧</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_15_ui_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_15_ui_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-red-500 mr-2 mt-1">🗑️</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_15_removed_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_15_removed_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- January 14, 2026 -->
                <div class="border-l-4 border-purple-500 pl-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('app.changelog.jan_14_title') }}</h3>
                        <span class="text-sm text-gray-500">{{ __('app.changelog.jan_14_date') }}</span>
                    </div>
                    <div class="space-y-3 text-gray-700">
                        <div class="flex items-start">
                            <span class="text-purple-500 mr-2 mt-1">🔓</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_14_registration_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_14_registration_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- January 11, 2026 -->
                <div class="border-l-4 border-green-500 pl-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('app.changelog.jan_11_title') }}</h3>
                        <span class="text-sm text-gray-500">{{ __('app.changelog.jan_11_date') }}</span>
                    </div>
                    <div class="space-y-3 text-gray-700">
                        <div class="flex items-start">
                            <span class="text-green-500 mr-2 mt-1">👨‍👩‍👧‍👦</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_11_family_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_11_family_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- January 10, 2026 -->
                <div class="border-l-4 border-indigo-500 pl-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('app.changelog.jan_10_title') }}</h3>
                        <span class="text-sm text-gray-500">{{ __('app.changelog.jan_10_date') }}</span>
                    </div>
                    <div class="space-y-3 text-gray-700">
                        <div class="flex items-start">
                            <span class="text-indigo-500 mr-2 mt-1">⭐</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_10_ratings_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_10_ratings_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-indigo-500 mr-2 mt-1">🎯</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_10_challenge_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_10_challenge_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-indigo-500 mr-2 mt-1">💾</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_10_backup_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_10_backup_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-indigo-500 mr-2 mt-1">📧</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_10_email_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_10_email_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-indigo-500 mr-2 mt-1">🌍</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_10_multilingual_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_10_multilingual_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">{{ __('app.changelog.info_title') }}</h3>
                    <p class="text-blue-800">{{ __('app.changelog.info_desc') }}</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
