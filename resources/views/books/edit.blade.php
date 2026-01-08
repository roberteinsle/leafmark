@extends('layouts.app')

@section('title', 'Edit Book')

@section('content')
<div class="px-4 max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('books.show', $book) }}" class="text-indigo-600 hover:text-indigo-700 flex items-center">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Book
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Edit Book</h1>

        <form method="POST" action="{{ route('books.update', $book) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                <input type="text" name="title" id="title" required value="{{ old('title', $book->title) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="author" class="block text-sm font-medium text-gray-700">Author</label>
                <input type="text" name="author" id="author" value="{{ old('author', $book->author) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('author') border-red-500 @enderror">
                @error('author')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="isbn" class="block text-sm font-medium text-gray-700">ISBN</label>
                    <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $book->isbn) }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('isbn') border-red-500 @enderror">
                    @error('isbn')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="isbn13" class="block text-sm font-medium text-gray-700">ISBN-13</label>
                    <input type="text" name="isbn13" id="isbn13" value="{{ old('isbn13', $book->isbn13) }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('isbn13') border-red-500 @enderror">
                    @error('isbn13')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="publisher" class="block text-sm font-medium text-gray-700">Publisher</label>
                <input type="text" name="publisher" id="publisher" value="{{ old('publisher', $book->publisher) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('publisher') border-red-500 @enderror">
                @error('publisher')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="published_date" class="block text-sm font-medium text-gray-700">Published Date</label>
                    <input type="date" name="published_date" id="published_date" value="{{ old('published_date', $book->published_date ? $book->published_date->format('Y-m-d') : '') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('published_date') border-red-500 @enderror">
                    @error('published_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="page_count" class="block text-sm font-medium text-gray-700">Page Count</label>
                    <input type="number" name="page_count" id="page_count" min="0" value="{{ old('page_count', $book->page_count) }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('page_count') border-red-500 @enderror">
                    @error('page_count')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="language" class="block text-sm font-medium text-gray-700">Language</label>
                <input type="text" name="language" id="language" maxlength="10" placeholder="e.g., en, de" value="{{ old('language', $book->language) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('language') border-red-500 @enderror">
                @error('language')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description', $book->description) }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>

                @if($book->cover_image)
                <div class="mb-4">
                    <div class="flex items-start gap-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-2">Current cover:</p>
                            <img src="{{ $book->cover_image }}" alt="{{ $book->title }}" class="w-32 h-48 object-cover rounded border border-gray-300">
                        </div>
                        @if($book->local_cover_path)
                        <div>
                            <form action="{{ route('books.delete-cover', $book) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this cover?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete Cover
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="space-y-2">
                    <label for="cover_image" class="block text-sm font-medium text-gray-700">Upload New Cover</label>

                    <!-- Drag & Drop Zone -->
                    <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-500 transition-colors cursor-pointer">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">
                            <span class="font-semibold text-indigo-600">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-xs text-gray-500 mt-1">JPEG, PNG, GIF, or WebP. Max 5MB.</p>
                        <p id="file-name" class="mt-2 text-sm text-gray-700 font-medium hidden"></p>
                    </div>

                    <input type="file" name="cover_image" id="cover_image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="hidden">

                    @error('cover_image')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="cover_url" class="block text-sm font-medium text-gray-700">Cover URL (for reference)</label>
                <input type="url" name="cover_url" id="cover_url" value="{{ old('cover_url', $book->cover_url) }}" readonly
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-600 @error('cover_url') border-red-500 @enderror">
                <p class="text-xs text-gray-500 mt-1">Original API cover URL (read-only)</p>
                @error('cover_url')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="thumbnail" class="block text-sm font-medium text-gray-700">Thumbnail URL (for reference)</label>
                <input type="url" name="thumbnail" id="thumbnail" value="{{ old('thumbnail', $book->thumbnail) }}" readonly
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-600 @error('thumbnail') border-red-500 @enderror">
                <p class="text-xs text-gray-500 mt-1">Original API thumbnail URL (read-only)</p>
                @error('thumbnail')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                <select name="status" id="status" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-500 @enderror">
                    <option value="want_to_read" {{ old('status', $book->status) === 'want_to_read' ? 'selected' : '' }}>Want to Read</option>
                    <option value="currently_reading" {{ old('status', $book->status) === 'currently_reading' ? 'selected' : '' }}>Currently Reading</option>
                    <option value="read" {{ old('status', $book->status) === 'read' ? 'selected' : '' }}>Read</option>
                </select>
                @error('status')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="format" class="block text-sm font-medium text-gray-700">Format</label>
                <select name="format" id="format"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('format') border-red-500 @enderror">
                    <option value="">-- Select Format --</option>
                    <option value="digital" {{ old('format', $book->format) === 'digital' ? 'selected' : '' }}>Digital</option>
                    <option value="paperback" {{ old('format', $book->format) === 'paperback' ? 'selected' : '' }}>Paperback</option>
                    <option value="hardcover" {{ old('format', $book->format) === 'hardcover' ? 'selected' : '' }}>Hardcover</option>
                    <option value="audiobook" {{ old('format', $book->format) === 'audiobook' ? 'selected' : '' }}>Audiobook</option>
                    <option value="magazine" {{ old('format', $book->format) === 'magazine' ? 'selected' : '' }}>Magazine</option>
                    <option value="spiral_bound" {{ old('format', $book->format) === 'spiral_bound' ? 'selected' : '' }}>Spiral Bound</option>
                    <option value="leather_bound" {{ old('format', $book->format) === 'leather_bound' ? 'selected' : '' }}>Leather Bound</option>
                    <option value="journal" {{ old('format', $book->format) === 'journal' ? 'selected' : '' }}>Journal</option>
                    <option value="comic" {{ old('format', $book->format) === 'comic' ? 'selected' : '' }}>Comic</option>
                    <option value="graphic_novel" {{ old('format', $book->format) === 'graphic_novel' ? 'selected' : '' }}>Graphic Novel</option>
                    <option value="manga" {{ old('format', $book->format) === 'manga' ? 'selected' : '' }}>Manga</option>
                    <option value="box_set" {{ old('format', $book->format) === 'box_set' ? 'selected' : '' }}>Box Set</option>
                    <option value="omnibus" {{ old('format', $book->format) === 'omnibus' ? 'selected' : '' }}>Omnibus</option>
                    <option value="reference" {{ old('format', $book->format) === 'reference' ? 'selected' : '' }}>Reference</option>
                    <option value="other" {{ old('format', $book->format) === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('format')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-1">
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date</label>
                    <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', $book->purchase_date ? $book->purchase_date->format('Y-m-d') : '') }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('purchase_date') border-red-500 @enderror">
                    @error('purchase_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1">
                    <label for="purchase_price" class="block text-sm font-medium text-gray-700">Purchase Price</label>
                    <input type="number" step="0.01" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', $book->purchase_price) }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('purchase_price') border-red-500 @enderror">
                    @error('purchase_price')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-1">
                    <label for="purchase_currency" class="block text-sm font-medium text-gray-700">Currency</label>
                    <select name="purchase_currency" id="purchase_currency"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('purchase_currency') border-red-500 @enderror">
                        <option value="EUR" {{ old('purchase_currency', $book->purchase_currency) === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="USD" {{ old('purchase_currency', $book->purchase_currency) === 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="GBP" {{ old('purchase_currency', $book->purchase_currency) === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                        <option value="CHF" {{ old('purchase_currency', $book->purchase_currency) === 'CHF' ? 'selected' : '' }}>CHF</option>
                    </select>
                    @error('purchase_currency')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="current_page" class="block text-sm font-medium text-gray-700">Current Page</label>
                <input type="number" name="current_page" id="current_page" min="0" value="{{ old('current_page', $book->current_page) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('current_page') border-red-500 @enderror">
                @error('current_page')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('books.show', $book) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-6 rounded-lg">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('cover_image');
    const fileNameDisplay = document.getElementById('file-name');

    // Click to upload
    dropZone.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT') {
            fileInput.click();
        }
    });

    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        handleFiles(this.files);
    });

    // Drag & Drop events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop zone when dragging over
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, function() {
            dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, function() {
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        });
    });

    // Handle dropped files
    dropZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            fileInput.files = files;
            handleFiles(files);
        }
    });

    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please upload a valid image file (JPEG, PNG, GIF, or WebP).');
                fileInput.value = '';
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB.');
                fileInput.value = '';
                return;
            }

            // Display file name
            fileNameDisplay.textContent = '✓ Selected: ' + file.name;
            fileNameDisplay.classList.remove('hidden');
            dropZone.classList.add('border-green-500');
        }
    }
});
</script>
@endpush
