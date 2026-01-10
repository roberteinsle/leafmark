@extends('layouts.app')

@section('title', 'Tags')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Tags</h1>
            <p class="mt-2 text-gray-600">Organize your books with custom tags</p>
        </div>
        <a href="{{ route('tags.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create New Tag
        </a>
    </div>

    @if($tags->isEmpty())
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-900">No tags yet</h3>
        <p class="mt-1 text-gray-500">Get started by creating your first tag.</p>
        <div class="mt-6">
            <a href="{{ route('tags.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Tag
            </a>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tags as $tag)
        @php
            // Map colors to lighter backgrounds with darker text
            $colorMap = [
                '#ef4444' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'icon' => '#ef4444'], // Red
                '#f97316' => ['bg' => '#ffedd5', 'text' => '#9a3412', 'icon' => '#f97316'], // Orange
                '#eab308' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'icon' => '#eab308'], // Yellow
                '#22c55e' => ['bg' => '#dcfce7', 'text' => '#166534', 'icon' => '#22c55e'], // Green
                '#06b6d4' => ['bg' => '#cffafe', 'text' => '#155e75', 'icon' => '#06b6d4'], // Cyan
                '#3b82f6' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'icon' => '#3b82f6'], // Blue
                '#6366f1' => ['bg' => '#e0e7ff', 'text' => '#3730a3', 'icon' => '#6366f1'], // Indigo
                '#a855f7' => ['bg' => '#f3e8ff', 'text' => '#6b21a8', 'icon' => '#a855f7'], // Purple
                '#ec4899' => ['bg' => '#fce7f3', 'text' => '#9f1239', 'icon' => '#ec4899'], // Pink
                '#64748b' => ['bg' => '#f1f5f9', 'text' => '#334155', 'icon' => '#64748b'], // Gray
            ];
            $colors = $colorMap[$tag->color] ?? ['bg' => '#e0e7ff', 'text' => '#3730a3', 'icon' => '#6366f1'];
        @endphp
        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="p-2 rounded-lg" style="background-color: {{ $colors['bg'] }}">
                                <svg class="w-5 h-5" style="color: {{ $colors['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $tag->name }}</h3>
                            @if($tag->is_default)
                            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded">Default</span>
                            @endif
                        </div>
                        @if($tag->description)
                        <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ $tag->description }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>{{ $tag->books_count }} {{ $tag->books_count === 1 ? 'book' : 'books' }}</span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('tags.show', $tag) }}" class="text-indigo-600 hover:text-indigo-700 p-2 rounded-lg hover:bg-indigo-50" title="View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        @if(!$tag->is_default)
                        <a href="{{ route('tags.edit', $tag) }}" class="text-gray-600 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                        <form action="{{ route('tags.destroy', $tag) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? All books will be removed from this tag.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
