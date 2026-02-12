<div class="bg-white rounded-lg shadow p-4 lg:sticky lg:top-6">
    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        {{ __('app.family.currently_reading_title') }}
    </h3>

    <div class="space-y-4">
        @foreach($familyReading as $book)
        <div class="flex gap-3">
            {{-- Book Cover --}}
            <div class="flex-shrink-0 w-12">
                @if($book->cover_image)
                    <img src="{{ $book->cover_image }}" alt="{{ $book->title }}" class="w-12 h-16 object-cover rounded shadow-sm">
                @else
                    <div class="w-12 h-16 bg-gray-200 rounded flex items-center justify-center shadow-sm">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Book Info --}}
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-indigo-600 truncate">{{ $book->user->name }}</p>
                <p class="text-sm font-semibold text-gray-900 truncate" title="{{ $book->title }}">{{ $book->title }}</p>

                @if($book->page_count)
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('app.family.page_x_of_y', ['current' => $book->current_page ?? 0, 'total' => $book->page_count]) }}
                    </p>
                    <div class="mt-1">
                        <div class="flex justify-between text-xs text-gray-400 mb-0.5">
                            <span>{{ $book->reading_progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ $book->reading_progress }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4 pt-3 border-t border-gray-100">
        <a href="{{ route('family.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
            {{ __('app.family.view_family') }} &rarr;
        </a>
    </div>
</div>
