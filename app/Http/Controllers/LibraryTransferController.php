<?php

namespace App\Http\Controllers;

use App\Models\ImportHistory;
use App\Services\LibraryExportService;
use App\Services\LibraryImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LibraryTransferController extends Controller
{
    public function export()
    {
        $service = new LibraryExportService(auth()->user());
        $zipPath = $service->createZipArchive();

        $filename = 'leafmark_export_' . now()->format('Y-m-d_His') . '.zip';

        return response()->download($zipPath, $filename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function showImportForm(): View
    {
        return view('library.import');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:51200', // 50MB
        ]);

        try {
            $file = $request->file('zip_file');
            $path = $file->store('imports/temp');

            $service = new LibraryImportService(auth()->user());
            $preview = $service->validateAndPreview(Storage::path($path));

            if (!$preview['valid']) {
                Storage::delete($path);
                return back()->withErrors(['zip_file' => $preview['errors'][0] ?? __('app.library_transfer.invalid_zip')]);
            }

            session(['library_import_path' => $path]);
            session(['library_import_filename' => $file->getClientOriginalName()]);

            return view('library.preview', [
                'preview' => $preview,
                'filename' => $file->getClientOriginalName(),
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['zip_file' => $e->getMessage()]);
        }
    }

    public function execute(Request $request): RedirectResponse
    {
        $request->validate([
            'duplicate_strategy' => 'required|in:skip,overwrite,keep_both',
        ]);

        $filePath = session('library_import_path');
        $filename = session('library_import_filename');

        if (!$filePath || !Storage::exists($filePath)) {
            return redirect()->route('library.import')
                ->withErrors(['error' => __('app.library_transfer.session_expired')]);
        }

        try {
            $service = new LibraryImportService(auth()->user());
            $importHistory = $service->import(
                Storage::path($filePath),
                $request->input('duplicate_strategy')
            );

            // Update filename
            $importHistory->update(['filename' => $filename]);

            Storage::delete($filePath);
            session()->forget(['library_import_path', 'library_import_filename']);

            return redirect()->route('library.import.result', $importHistory->id)
                ->with('success', __('app.library_transfer.import_completed'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(): RedirectResponse
    {
        $filePath = session('library_import_path');

        if ($filePath && Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        session()->forget(['library_import_path', 'library_import_filename']);

        return redirect()->route('settings.edit', ['tab' => 'data'])
            ->with('info', __('app.library_transfer.import_cancelled'));
    }

    public function result(ImportHistory $importHistory): View
    {
        if ($importHistory->user_id !== auth()->id()) {
            abort(403);
        }

        return view('library.result', compact('importHistory'));
    }
}
