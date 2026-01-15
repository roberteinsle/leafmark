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

                <!-- January 15, 2026 - Table View -->
                <div class="border-l-4 border-purple-500 pl-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('app.changelog.jan_15_table_view_title') }}</h3>
                        <span class="text-sm text-gray-500">{{ __('app.changelog.jan_15_table_view_date') }}</span>
                    </div>
                    <div class="space-y-3 text-gray-700">
                        <div class="flex items-start">
                            <span class="text-purple-500 mr-2 mt-1">📊</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_15_table_view_mode_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_15_table_view_mode_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-purple-500 mr-2 mt-1">⚙️</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_15_table_columns_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_15_table_columns_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-purple-500 mr-2 mt-1">💾</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_15_table_persistence_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_15_table_persistence_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- January 16, 2026 -->
                <div class="border-l-4 border-green-500 pl-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('app.changelog.jan_16_title') }}</h3>
                        <span class="text-sm text-gray-500">{{ __('app.changelog.jan_16_date') }}</span>
                    </div>
                    <div class="space-y-3 text-gray-700">
                        <div class="flex items-start">
                            <span class="text-green-500 mr-2 mt-1">🔍</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_16_author_filter_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_16_author_filter_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="text-green-500 mr-2 mt-1">📋</span>
                            <div>
                                <strong>{{ __('app.changelog.jan_16_changelog_title') }}</strong>
                                <p class="text-sm text-gray-600">{{ __('app.changelog.jan_16_changelog_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

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
                    <a href="https://github.com/roberteinsle/leafmark/commits/main/"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center mt-3 text-blue-600 hover:text-blue-800 font-medium">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('app.changelog.view_github') }}
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
