@extends('layouts.app')

@section('title', __('app.import.title'))

@section('content')
<div class="px-4 max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('app.import.title') }}</h1>
        <p class="mt-2 text-gray-600">{{ __('app.import.description') }}</p>
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

    <!-- Upload Form -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('app.import.upload_csv') }}</h2>
        
        <form action="{{ localeRoute('import.upload') }}" method="POST" enctype="multipart/form-data" 
              x-data="{ fileName: '', dragging: false }"
              @drop.prevent="dragging = false; handleDrop($event)"
              @dragover.prevent="dragging = true"
              @dragleave.prevent="dragging = false">
            @csrf

            <div class="mb-6">
                <div :class="dragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'"
                     class="border-2 border-dashed rounded-lg p-8 text-center transition-colors">
                    <input type="file" 
                           name="csv_file" 
                           id="csv_file" 
                           accept=".csv,text/csv" 
                           class="hidden"
                           @change="fileName = $event.target.files[0]?.name || ''">
                    
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    
                    <div class="mt-4">
                        <label for="csv_file" class="cursor-pointer">
                            <span class="text-indigo-600 hover:text-indigo-700 font-medium">{{ __('app.import.click_to_upload') }}</span>
                            <span class="text-gray-600"> {{ __('app.import.or_drag_and_drop') }}</span>
                        </label>
                        <p class="text-sm text-gray-500 mt-2">{{ __('app.import.file_requirements') }}</p>
                    </div>
                    
                    <p x-show="fileName" x-text="fileName" class="mt-4 text-sm font-medium text-gray-900"></p>
                </div>
            </div>

            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-900 mb-2">{{ __('app.import.how_to_export') }}</h3>
                <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                    <li>{{ __('app.import.step_1') }}</li>
                    <li>{{ __('app.import.step_2') }}</li>
                    <li>{{ __('app.import.step_3') }}</li>
                    <li>{{ __('app.import.step_4') }}</li>
                </ol>
            </div>

            <button type="submit" 
                    :disabled="!fileName"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium py-3 px-6 rounded-lg transition-colors">
                {{ __('app.import.preview_import') }}
            </button>
        </form>
    </div>

    <!-- Recent Imports -->
    @if($imports->count() > 0)
    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">{{ __('app.import.recent_imports') }}</h2>
            <a href="{{ localeRoute('import.history') }}" class="text-indigo-600 hover:text-indigo-700 text-sm">
                {{ __('app.import.view_all') }} →
            </a>
        </div>

        <div class="space-y-4">
            @foreach($imports as $import)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-medium text-gray-900">{{ $import->filename }}</span>
                            @if($import->status === 'completed')
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">{{ __('app.import.status_completed') }}</span>
                            @elseif($import->status === 'processing')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ __('app.import.status_processing') }}</span>
                            @elseif($import->status === 'failed')
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">{{ __('app.import.status_failed') }}</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ __('app.import.imported_on') }}: {{ $import->created_at->format('M d, Y H:i') }}
                        </div>
                        @if($import->status === 'completed')
                        <div class="text-sm text-gray-600 mt-1">
                            {{ __('app.import.stats', [
                                'imported' => $import->imported_count,
                                'skipped' => $import->skipped_count,
                                'failed' => $import->failed_count,
                                'total' => $import->total_rows
                            ]) }}
                        </div>
                        @endif
                    </div>
                    @if($import->status === 'completed')
                    <a href="{{ localeRoute('import.result', $import) }}" 
                       class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        {{ __('app.import.view_details') }}
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{ $imports->links() }}
    </div>
    @endif
</div>

<script>
function handleDrop(event) {
    const file = event.dataTransfer.files[0];
    if (file && (file.type === 'text/csv' || file.name.endsWith('.csv'))) {
        document.getElementById('csv_file').files = event.dataTransfer.files;
        // Trigger change event
        const changeEvent = new Event('change', { bubbles: true });
        document.getElementById('csv_file').dispatchEvent(changeEvent);
    }
}
</script>
@endsection
