<?php

namespace App\Http\Controllers;

use App\Models\ImportHistory;
use App\Services\GoodreadsImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show import upload form
     */
    public function index()
    {
        $imports = auth()->user()->importHistory()->latest()->paginate(10);
        return view('import.index', compact('imports'));
    }

    /**
     * Show import history
     */
    public function history()
    {
        $imports = auth()->user()->importHistory()->latest()->paginate(20);
        return view('import.history', compact('imports'));
    }

    /**
     * Upload and preview CSV
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('csv_file');
            $path = $file->store('imports/temp');

            $service = new GoodreadsImportService(auth()->user());
            $preview = $service->parseCSV(Storage::path($path));

            // Store file path in session for actual import
            session(['import_file_path' => $path]);
            session(['import_filename' => $file->getClientOriginalName()]);

            return view('import.preview', [
                'preview' => $preview['preview'],
                'total_rows' => $preview['total_rows'],
                'filename' => $file->getClientOriginalName(),
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['csv_file' => 'Error processing file: ' . $e->getMessage()]);
        }
    }

    /**
     * Confirm and execute import
     */
    public function execute(Request $request)
    {
        $filePath = session('import_file_path');
        $filename = session('import_filename');

        if (!$filePath || !Storage::exists($filePath)) {
            return redirect()->route('import.index')
                ->withErrors(['error' => 'Import session expired. Please upload the file again.']);
        }

        try {
            $service = new GoodreadsImportService(auth()->user());
            $importHistory = $service->import(Storage::path($filePath), $filename);

            // Clean up temp file
            Storage::delete($filePath);
            session()->forget(['import_file_path', 'import_filename']);

            return redirect()->route('import.result', $importHistory->id)
                ->with('success', 'Import completed successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Show import result
     */
    public function result(ImportHistory $importHistory)
    {
        // Ensure user owns this import
        if ($importHistory->user_id !== auth()->id()) {
            abort(403);
        }

        return view('import.result', compact('importHistory'));
    }

    /**
     * Cancel import (delete temp file)
     */
    public function cancel()
    {
        $filePath = session('import_file_path');
        
        if ($filePath && Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        session()->forget(['import_file_path', 'import_filename']);

        return redirect()->route('import.index')
            ->with('info', 'Import cancelled.');
    }

    /**
     * Delete import history record
     */
    public function destroy(ImportHistory $importHistory)
    {
        // Ensure user owns this import
        if ($importHistory->user_id !== auth()->id()) {
            abort(403);
        }

        $importHistory->delete();

        return redirect()->route('import.history')
            ->with('success', 'Import history deleted.');
    }
}
