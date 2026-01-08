@extends('layouts.app')

@section('title', 'Edit Tag')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('tags.index') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center">
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

        <form method="POST" action="{{ route('tags.update', $tag) }}" class="space-y-6">
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
                <label for="color" class="block text-sm font-medium text-gray-700">Tag Color</label>
                <div class="mt-1 flex items-center gap-3">
                    <input type="color" name="color" id="color" value="{{ old('color', $tag->color ?? '#6366f1') }}"
                           class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                    <span class="text-sm text-gray-500">Choose a color for this tag</span>
                </div>
                @error('color')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-4 pt-6">
                <a href="{{ route('tags.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-6 rounded-lg">
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
