<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(config('services.google_site_verification'))
    <meta name="google-site-verification" content="{{ config('services.google_site_verification') }}" />
    @endif
    <title>{{ config('app.name', 'Leafmark') }} - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('head')
</head>
<body class="bg-gray-50 flex flex-col min-h-screen @auth pb-16 sm:pb-0 @endauth">
    @auth
    @php
        // Hauptbereiche: oben auf dem Desktop, als Bottom-Tab-Bar auf dem Handy (Standard Abschnitt 10)
        $navItems = [
            ['route' => 'books.index', 'active' => ['books.*', 'series.*'], 'label' => __('app.nav.books'),
             'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['route' => 'tags.index', 'active' => ['tags.*'], 'label' => __('app.nav.tags'),
             'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
            ['route' => 'challenge.index', 'active' => ['challenge.*'], 'label' => __('app.nav.challenge'),
             'icon' => 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9'],
            ['route' => 'stats.index', 'active' => ['stats.*'], 'label' => __('app.nav.statistics'),
             'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['route' => 'family.index', 'active' => ['family.*'], 'label' => __('app.nav.family'),
             'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ];
    @endphp
    @endauth
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ url('/') }}" class="text-xl font-bold text-gray-800">
                            📚 {{ config('app.name', 'Leafmark') }}
                        </a>
                    </div>
                    @auth
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        @foreach($navItems as $item)
                            @php($isActive = request()->routeIs(...$item['active']))
                            <a href="{{ route($item['route']) }}"
                               @if($isActive) aria-current="page" @endif
                               class="{{ $isActive ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                    @endauth
                </div>
                <div class="flex items-center">
                    @auth
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('books.create') }}" aria-label="{{ __('app.nav.add_book') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="h-4 w-4 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="hidden sm:inline">{{ __('app.nav.add_book') }}</span>
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
                    &copy; {{ date('Y') }} Leafmark
                </p>
                <p class="text-center text-gray-400 text-xs mt-1">
                    v{{ config('app.version') }}@if(config('app.commit_hash') && config('app.commit_hash') !== 'unknown') ({{ config('app.commit_hash') }})@endif
                </p>
            </div>
        </div>
    </footer>

    @auth
    <!-- Mobile: Bottom-Tab-Bar mit den Hauptbereichen -->
    <nav class="sm:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-200" style="padding-bottom: env(safe-area-inset-bottom);" aria-label="{{ __('app.nav.main') }}">
        <div class="grid grid-cols-5">
            @foreach($navItems as $item)
                @php($isActive = request()->routeIs(...$item['active']))
                <a href="{{ route($item['route']) }}"
                   @if($isActive) aria-current="page" @endif
                   class="flex flex-col items-center justify-center py-2 text-xs font-medium {{ $isActive ? 'text-blue-600' : 'text-gray-500' }}">
                    <svg class="h-6 w-6 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span class="truncate max-w-full px-1">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
    @endauth

    @stack('scripts')
</body>
</html>
