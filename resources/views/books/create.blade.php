@extends('layouts.app')

@section('title', __('app.books.add_new_book'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.books.add_new_book') }}</h1>
        <p class="mt-2 text-gray-600">{{ __('app.books.search_for_book') }}</p>
    </div>

    <!-- Search Form -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <form action="{{ localeRoute('books.create') }}" method="GET" class="space-y-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.books.search_for_books') }}
                </label>
                <div class="flex gap-2">
                    <input type="text"
                           name="q"
                           id="search"
                           value="{{ $searchQuery ?? '' }}"
                           placeholder="{{ __('app.books.search_placeholder') }}"
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           autofocus>
                    <select name="provider"
                            class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @php
                            $defaultProvider = 'both';
                            $hasGoogle = auth()->user()->google_books_api_key || config('services.google_books.api_key');
                            $hasBigBook = \App\Models\SystemSetting::get('bigbook_api_key');
                            if (!$hasGoogle && !$hasBigBook) {
                                $defaultProvider = 'openlibrary';
                            }
                        @endphp
                        <option value="openlibrary" {{ request('provider', $defaultProvider) === 'openlibrary' ? 'selected' : '' }}>{{ __('app.books.openlibrary') }}</option>
                        <option value="bookbrainz" {{ request('provider', $defaultProvider) === 'bookbrainz' ? 'selected' : '' }}>BookBrainz</option>
                        @if($hasBigBook)
                        <option value="bigbook" {{ request('provider', $defaultProvider) === 'bigbook' ? 'selected' : '' }}>Big Book API</option>
                        @endif
                        @if($hasGoogle)
                        <option value="google" {{ request('provider', $defaultProvider) === 'google' ? 'selected' : '' }}>{{ __('app.books.google_books') }}</option>
                        @endif
                        @if($hasGoogle || $hasBigBook)
                        <option value="both" {{ request('provider', $defaultProvider) === 'both' ? 'selected' : '' }}>{{ __('app.books.all_sources') }}</option>
                        @endif
                    </select>
                    <select name="lang"
                            class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="{{ auth()->user()->preferred_language ?? 'en' }}" {{ !request('lang') ? 'selected' : '' }}>
                            {{ auth()->user()->preferred_language === 'de' ? 'Deutsch' : 'English' }} (Default)
                        </option>
                        <option value="en" {{ request('lang') === 'en' ? 'selected' : '' }}>English</option>
                        <option value="de" {{ request('lang') === 'de' ? 'selected' : '' }}>Deutsch</option>
                    </select>
                    <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('app.books.search') }}
                    </button>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    💡 {{ __('app.books.search_tip') }}<br>
                    {{ __('app.books.search_by_identifier') }} <code class="bg-gray-100 px-1 rounded">isbn:9783...</code>, <code class="bg-gray-100 px-1 rounded">ol:OL123M</code>, <code class="bg-gray-100 px-1 rounded">goodreads:456</code>, <code class="bg-gray-100 px-1 rounded">librarything:789</code>
                </p>
            </div>
        </form>
    </div>

    @if(isset($searchQuery) && $searchQuery)
        @if(!empty($searchResults))
            <!-- Search Results -->
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('app.books.search_results') }}</h2>

                @foreach($searchResults as $result)
                    <div class="bg-white rounded-lg shadow-sm p-6 flex gap-6">
                        @if($result['thumbnail'])
                            <img src="{{ $result['thumbnail'] }}"
                                 alt="{{ $result['title'] }}"
                                 class="w-24 h-32 object-cover rounded">
                        @else
                            <div class="w-24 h-32 bg-gray-200 rounded flex items-center justify-center">
                                <span class="text-gray-400 text-4xl">📚</span>
                            </div>
                        @endif

                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $result['title'] }}</h3>
                            @if($result['author'])
                                <p class="text-gray-600">{{ __('app.books.by_author') }}
                                    <a href="{{ localeRoute('books.create', ['q' => 'author:' . $result['author'], 'lang' => request('lang', auth()->user()->preferred_language ?? 'en')]) }}"
                                       class="text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ $result['author'] }}
                                    </a>
                                </p>
                            @endif

                            <div class="mt-2 space-y-1 text-sm text-gray-500">
                                @if($result['publisher'])
                                    <p>{{ __('app.books.publisher') }}: {{ $result['publisher'] }}</p>
                                @endif
                                @if($result['published_date'])
                                    <p>{{ __('app.books.published_date') }}: {{ $result['published_date'] }}</p>
                                @endif
                                @if($result['page_count'])
                                    <p>{{ __('app.books.pages') }}: {{ $result['page_count'] }}</p>
                                @endif
                                @if($result['isbn13'] || $result['isbn'])
                                    <p>{{ __('app.books.isbn') }}: {{ $result['isbn13'] ?? $result['isbn'] }}</p>
                                @endif
                            </div>

                            @if($result['description'])
                                <p class="mt-3 text-sm text-gray-600 line-clamp-3">
                                    {{ Str::limit(strip_tags($result['description']), 200) }}
                                </p>
                            @endif

                            <form action="{{ localeRoute('books.store-from-api') }}" method="POST" class="mt-4">
                                @csrf
                                <input type="hidden" name="source" value="{{ $result['source'] }}">
                                @if($result['source'] === 'google')
                                    <input type="hidden" name="google_books_id" value="{{ $result['google_books_id'] }}">
                                @elseif($result['source'] === 'amazon')
                                    <input type="hidden" name="amazon_asin" value="{{ $result['asin'] }}">
                                @elseif($result['source'] === 'bookbrainz')
                                    <input type="hidden" name="bookbrainz_id" value="{{ $result['bookbrainz_id'] }}">
                                @elseif($result['source'] === 'bigbook')
                                    <input type="hidden" name="bigbook_id" value="{{ $result['bigbook_id'] }}">
                                @else
                                    <input type="hidden" name="open_library_id" value="{{ $result['open_library_id'] }}">
                                @endif

                                <div class="flex gap-3 items-center">
                                    <select name="status" required class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        <option value="want_to_read">{{ __('app.books.want_to_read') }}</option>
                                        <option value="currently_reading">{{ __('app.books.currently_reading') }}</option>
                                        <option value="read">{{ __('app.books.read') }}</option>
                                    </select>

                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">
                                        {{ __('app.books.add_to_library') }}
                                    </button>

                                    <span class="text-xs text-gray-400">
                                        @if($result['source'] === 'google')
                                            {{ __('app.books.google_books') }}
                                        @elseif($result['source'] === 'amazon')
                                            Amazon
                                        @elseif($result['source'] === 'bookbrainz')
                                            BookBrainz
                                        @elseif($result['source'] === 'bigbook')
                                            Big Book API
                                        @else
                                            {{ __('app.books.openlibrary') }}
                                        @endif
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($noResults)
            <!-- No Results - Show Manual Form -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-yellow-900 mb-2">{{ __('app.books.no_results_found') }}</h3>
                <p class="text-yellow-700">{{ __('app.books.no_results_message') }}</p>
            </div>

            <!-- Manual Book Entry Form -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('app.books.add_book_manually') }}</h2>

                <form action="{{ localeRoute('books.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700">{{ __('app.books.title_label') }} *</label>
                            <input type="text" name="title" id="title" required value="{{ old('title', $searchQuery) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="author" class="block text-sm font-medium text-gray-700">{{ __('app.books.author') }}</label>
                            <input type="text" name="author" id="author" value="{{ old('author') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">{{ __('app.books.status') }} *</label>
                            <select name="status" id="status" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="want_to_read">{{ __('app.books.want_to_read') }}</option>
                                <option value="currently_reading">{{ __('app.books.currently_reading') }}</option>
                                <option value="read">{{ __('app.books.read') }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="isbn" class="block text-sm font-medium text-gray-700">{{ __('app.books.isbn') }}</label>
                            <input type="text" name="isbn" id="isbn" value="{{ old('isbn') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="isbn13" class="block text-sm font-medium text-gray-700">{{ __('app.books.isbn13') }}</label>
                            <input type="text" name="isbn13" id="isbn13" value="{{ old('isbn13') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="publisher" class="block text-sm font-medium text-gray-700">{{ __('app.books.publisher') }}</label>
                            <input type="text" name="publisher" id="publisher" value="{{ old('publisher') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="published_date" class="block text-sm font-medium text-gray-700">{{ __('app.books.published_date') }}</label>
                            <input type="date" name="published_date" id="published_date" value="{{ old('published_date') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="page_count" class="block text-sm font-medium text-gray-700">{{ __('app.books.page_count') }}</label>
                            <input type="number" name="page_count" id="page_count" value="{{ old('page_count') }}" min="0"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700">{{ __('app.books.language') }}</label>
                            <input type="text" name="language" id="language" value="{{ old('language', 'en') }}" maxlength="10"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('app.books.description') }}</label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label for="cover_url" class="block text-sm font-medium text-gray-700">Cover Image URL</label>
                            <input type="url" name="cover_url" id="cover_url" value="{{ old('cover_url') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="thumbnail" class="block text-sm font-medium text-gray-700">Thumbnail URL</label>
                            <input type="url" name="thumbnail" id="thumbnail" value="{{ old('thumbnail') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ localeRoute('books.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            {{ __('app.settings.cancel') }}
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">
                            {{ __('app.books.add_new_book') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endif
</div>
@endsection
