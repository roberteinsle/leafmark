<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="P83A54mzktESaX8bAfsLcUk9Ex-jUq5TNtUMmO59gaQ" />

    <!-- SEO Meta Tags -->
    <title>{{ config('app.name') }} - {{ __('app.welcome.meta_title') }}</title>
    <meta name="description" content="{{ __('app.welcome.meta_description') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ config('app.url') }}">

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">📚 {{ config('app.name') }}</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900">{{ __('app.nav.login') }}</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">{{ __('app.welcome.get_started') }}</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-grow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h2 class="text-4xl font-extrabold text-gray-900 sm:text-5xl md:text-6xl">{{ __('app.welcome.hero_title') }}</h2>
                    <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">{{ __('app.welcome.hero_subtitle') }}</p>
                    <div class="mt-5 max-w-2xl mx-auto sm:flex sm:justify-center md:mt-8">
                        <div class="rounded-md shadow flex-1 sm:max-w-xs">
                            <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-12 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-16 whitespace-nowrap">{{ __('app.welcome.start_tracking') }}</a>
                        </div>
                        <div class="mt-3 rounded-md shadow flex-1 sm:max-w-xs sm:mt-0 sm:ml-3">
                            <a href="{{ route('login') }}" class="w-full flex items-center justify-center px-12 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-16">{{ __('app.welcome.sign_in') }}</a>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="mt-20">
                    <h3 class="text-3xl font-bold text-gray-900 text-center mb-12">{{ __('app.welcome.features_title') }}</h3>
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">📖</div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.welcome.feature1_title') }}</h3>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('app.welcome.feature1_desc') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">🔍</div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.welcome.feature2_title') }}</h3>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('app.welcome.feature2_desc') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">🏷️</div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.welcome.feature3_title') }}</h3>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('app.welcome.feature3_desc') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">📊</div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.welcome.feature4_title') }}</h3>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('app.welcome.feature4_desc') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">🎯</div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.welcome.feature5_title') }}</h3>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('app.welcome.feature5_desc') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">👥</div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.welcome.feature6_title') }}</h3>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('app.welcome.feature6_desc') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">🌍</div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.welcome.feature7_title') }}</h3>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('app.welcome.feature7_desc') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">📚</div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.welcome.feature8_title') }}</h3>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('app.welcome.feature8_desc') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="text-center">
                                    <div class="text-4xl mb-4">⭐</div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.welcome.feature9_title') }}</h3>
                                    <p class="mt-2 text-sm text-gray-500">{{ __('app.welcome.feature9_desc') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-20">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center space-y-4">
                    <div class="flex space-x-6 text-sm">
                    </div>
                    <p class="text-center text-gray-500 text-sm">{{ __('app.welcome.footer') }}</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
