<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="P83A54mzktESaX8bAfsLcUk9Ex-jUq5TNtUMmO59gaQ" />
    <title>{{ config('app.name', 'Leafmark') }} - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @yield('head')
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('books.index') }}" class="text-xl font-bold text-gray-800">
                            📚 {{ config('app.name', 'Leafmark') }}
                        </a>
                    </div>
                    @auth
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('books.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('app.nav.books') }}
                        </a>
                        <a href="{{ route('tags.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('app.nav.tags') }}
                        </a>
                        <a href="{{ route('challenge.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('app.nav.challenge') }}
                        </a>
                        <a href="{{ route('stats.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('app.nav.statistics') }}
                        </a>
                        <a href="{{ route('family.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('app.nav.family') }}
                        </a>
                    </div>
                    @endauth
                </div>
                <div class="flex items-center">
                    @auth
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('books.create') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.nav.add_book') }}
                        </a>

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center text-sm text-gray-700 hover:text-gray-900 focus:outline-none">
                                <span class="mr-1">{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-50"
                                 style="display: none;">
                                @if(Auth::user()->is_admin)
                                <a href="{{ route('admin.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-semibold">
                                    ⚙️ {{ __('app.admin.title') }}
                                </a>
                                <hr class="my-1">
                                @endif
                                <a href="{{ route('settings.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ __('app.nav.settings') }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {{ __('app.nav.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="space-x-4">
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">{{ __('app.nav.login') }}</a>
                        <a href="{{ route('register') }}" class="text-sm text-gray-700 hover:text-gray-900">{{ __('app.nav.register') }}</a>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="py-10 flex-grow">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center">
                <p class="text-center text-gray-500 text-sm">
                    &copy; {{ date('Y') }} Leafmark. Made with ❤️ in Hamburg
                </p>
                <p class="text-center text-gray-400 text-xs mt-1">
                    v{{ config('app.version') }}@if(config('app.commit_hash') && config('app.commit_hash') !== 'unknown') ({{ config('app.commit_hash') }})@endif
                </p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
