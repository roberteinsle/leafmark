@extends('layouts.app')

@section('title', __('app.challenge.title'))

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.challenge.title') }} {{ $currentYear }}</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Challenge Goal Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        @if($challenge)
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('app.challenge.your_goal') }} {{ $currentYear }}</h2>
                    <div class="flex items-center gap-3" x-data="{ editing: false }">
                        <form method="POST" action="{{ route('challenge.update', $challenge) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <div x-show="editing" class="flex items-center gap-2">
                                <input type="number" name="goal" value="{{ $challenge->goal }}" min="1" max="1000" class="w-20 px-3 py-2 border border-gray-300 rounded-md">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                                    {{ __('app.challenge.update_goal') }}
                                </button>
                            </div>
                            <button type="button" @click="editing = !editing" x-show="!editing" class="text-indigo-600 hover:text-indigo-700 text-sm">
                                {{ __('app.challenge.edit_goal') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('challenge.destroy', $challenge) }}" onsubmit="return confirm('{{ __('app.challenge.delete_confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-700 text-sm">
                                {{ __('app.challenge.delete_challenge') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>{{ __('app.challenge.progress') }}</span>
                        <span>{{ $challenge->progress }} {{ __('app.challenge.of') }} {{ $challenge->goal }} {{ __('app.challenge.books_read') }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-indigo-600 h-4 rounded-full transition-all duration-300"
                             style="width: {{ $challenge->progress_percentage }}%"></div>
                    </div>
                    <div class="text-right mt-1">
                        <span class="text-2xl font-bold text-indigo-600">{{ $challenge->progress_percentage }}%</span>
                    </div>
                </div>

                @if($challenge->is_completed)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <p class="text-green-800 font-semibold text-lg">Glückwunsch! Du hast dein Ziel erreicht! </p>
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('app.challenge.your_goal') }} {{ $currentYear }}</h2>
                <form method="POST" action="{{ route('challenge.store') }}" class="inline-flex items-center gap-3">
                    @csrf
                    <input type="number" name="goal" placeholder="z.B. 24" min="1" max="1000" required
                           class="w-32 px-4 py-2 border border-gray-300 rounded-md text-center">
                    <span class="text-gray-600">{{ __('app.challenge.books_goal') }}</span>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                        {{ __('app.challenge.set_goal') }}
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Monthly Achievements -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('app.challenge.monthly_achievements') }}</h2>
        <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @php
                $months = [
                    1 => 'january', 2 => 'february', 3 => 'march', 4 => 'april',
                    5 => 'may', 6 => 'june', 7 => 'july', 8 => 'august',
                    9 => 'september', 10 => 'october', 11 => 'november', 12 => 'december'
                ];
            @endphp
            @foreach($months as $monthNum => $monthKey)
                <div class="text-center p-4 rounded-lg {{ $monthlyStats[$monthNum] > 0 ? 'bg-indigo-50' : 'bg-gray-50' }}">
                    <div class="text-3xl mb-1">
                        @if($monthlyStats[$monthNum] >= 5)

                        @elseif($monthlyStats[$monthNum] >= 3)

                        @elseif($monthlyStats[$monthNum] >= 1)

                        @else
                            <span class="text-gray-300"></span>
                        @endif
                    </div>
                    <div class="text-xs font-medium text-gray-600">{{ __('app.challenge.' . $monthKey) }}</div>
                    <div class="text-lg font-bold {{ $monthlyStats[$monthNum] > 0 ? 'text-indigo-600' : 'text-gray-400' }}">
                        {{ $monthlyStats[$monthNum] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Books Read This Year -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('app.challenge.books_read_this_year') }}</h2>
        @if($booksReadThisYear->isEmpty())
            <p class="text-gray-500 text-center py-8">{{ __('app.challenge.no_books_finished') }}</p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                @foreach($booksReadThisYear as $book)
                <div class="bg-white rounded-lg hover:shadow-lg transition">
                    <a href="{{ route('books.show', $book) }}" class="block">
                        @if($book->cover_image)
                            <img src="{{ $book->cover_image }}" alt="{{ $book->title }}" class="w-full aspect-[2/3] object-cover rounded-t-lg">
                        @else
                            <div class="w-full aspect-[2/3] bg-gray-200 rounded-t-lg flex items-center justify-center">
                                <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        @endif
                        <div class="p-3">
                            <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-1">{{ $book->title }}</h3>
                            @if($book->author)
                                <p class="text-xs text-gray-600 line-clamp-1">{{ $book->author }}</p>
                            @endif
                            @if($book->finished_at)
                                <p class="text-xs text-gray-500 mt-1">{{ $book->finished_at->format('M Y') }}</p>
                            @endif
                            @if($book->rating)
                                <div class="flex items-center mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-3 h-3 {{ $book->rating >= $i ? 'text-yellow-400' : 'text-gray-300' }} fill-current"
                                             viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    @endfor
                                </div>
                            @endif
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
