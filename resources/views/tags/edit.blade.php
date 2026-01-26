@extends('layouts.app')

@section('title', 'Edit Tag')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ localeRoute('tags.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Tags
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="flex justify-between items-start mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Tag</h1>
            @if($tag->is_default)
            <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-600 rounded">Default Tag</span>
            @endif
        </div>

        <form method="POST" action="{{ localeRoute('tags.update', $tag) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Tag Name *</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $tag->name) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $tag->description) }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tag Color</label>
                <div class="grid grid-cols-5 gap-3">
                    @php
                    $colors = [
                        '#ef4444' => 'Red',
                        '#f97316' => 'Orange',
                        '#eab308' => 'Yellow',
                        '#22c55e' => 'Green',
                        '#06b6d4' => 'Cyan',
                        '#3b82f6' => 'Blue',
                        '#6366f1' => 'Indigo',
                        '#a855f7' => 'Purple',
                        '#ec4899' => 'Pink',
                        '#64748b' => 'Gray',
                    ];
                    $selectedColor = old('color', $tag->color ?? '#6366f1');
                    @endphp

                    @foreach($colors as $colorValue => $colorName)
                    <label class="flex flex-col items-center cursor-pointer">
                        <input type="radio" name="color" value="{{ $colorValue }}"
                               {{ $selectedColor === $colorValue ? 'checked' : '' }}
                               class="sr-only peer" required>
                        <div class="w-12 h-12 rounded-lg border-2 border-gray-300 peer-checked:border-gray-900 peer-checked:ring-2 peer-checked:ring-gray-900 transition-all"
                             style="background-color: {{ $colorValue }}"></div>
                        <span class="text-xs text-gray-600 mt-1">{{ $colorName }}</span>
                    </label>
                    @endforeach
                </div>
                @error('color')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-4 pt-6">
                <a href="{{ localeRoute('tags.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-6 rounded-lg">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
