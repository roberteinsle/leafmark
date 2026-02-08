@extends('layouts.app')

@section('title', __('app.library_transfer.import_title'))

@section('content')
<div class="px-4 max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.library_transfer.import_title') }}</h1>
        <p class="mt-2 text-gray-600">{{ __('app.library_transfer.import_description') }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('app.library_transfer.upload_zip') }}</h2>

        <form action="{{ route('library.import.upload') }}" method="POST" enctype="multipart/form-data"
              x-data="{ fileName: '', dragging: false }"
              @drop.prevent="dragging = false; handleZipDrop($event)"
              @dragover.prevent="dragging = true"
              @dragleave.prevent="dragging = false">
            @csrf

            <div class="mb-6">
                <div :class="dragging ? 'border-green-500 bg-green-50' : 'border-gray-300'"
                     class="border-2 border-dashed rounded-lg p-8 text-center transition-colors">
                    <input type="file"
                           name="zip_file"
                           id="zip_file"
                           accept=".zip,application/zip"
                           class="hidden"
                           @change="fileName = $event.target.files[0]?.name || ''">

                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>

                    <div class="mt-4">
                        <label for="zip_file" class="cursor-pointer">
                            <span class="text-green-600 hover:text-green-700 font-medium">{{ __('app.library_transfer.click_to_upload') }}</span>
                            <span class="text-gray-600"> {{ __('app.library_transfer.or_drag_and_drop') }}</span>
                        </label>
                        <p class="text-sm text-gray-500 mt-2">{{ __('app.library_transfer.file_requirements') }}</p>
                    </div>

                    <p x-show="fileName" x-text="fileName" class="mt-4 text-sm font-medium text-gray-900"></p>
                </div>
            </div>

            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-900 mb-2">{{ __('app.library_transfer.import_info') }}</h3>
                <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                    <li>{{ __('app.library_transfer.import_step_1') }}</li>
                    <li>{{ __('app.library_transfer.import_step_2') }}</li>
                    <li>{{ __('app.library_transfer.import_step_3') }}</li>
                </ol>
            </div>

            <button type="submit"
                    :disabled="!fileName"
                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium py-3 px-6 rounded-lg transition-colors">
                {{ __('app.library_transfer.preview_import') }}
            </button>
        </form>
    </div>

    <div class="text-center">
        <a href="{{ route('settings.edit', ['tab' => 'data']) }}" class="text-gray-500 hover:text-gray-700 text-sm">
            &larr; {{ __('app.library_transfer.back_to_settings') }}
        </a>
    </div>
</div>

<script>
function handleZipDrop(event) {
    const file = event.dataTransfer.files[0];
    if (file && (file.type === 'application/zip' || file.name.endsWith('.zip'))) {
        document.getElementById('zip_file').files = event.dataTransfer.files;
        const changeEvent = new Event('change', { bubbles: true });
        document.getElementById('zip_file').dispatchEvent(changeEvent);
    }
}
</script>
@endsection
