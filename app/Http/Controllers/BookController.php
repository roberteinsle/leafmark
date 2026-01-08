<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = auth()->user()->books();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $books = $query->orderBy('added_at', 'desc')
            ->paginate(20);

        return view('books.index', compact('books'));
    }

    public function create(): View
    {
        return view('books.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'isbn13' => 'nullable|string|max:20',
            'publisher' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
            'description' => 'nullable|string',
            'page_count' => 'nullable|integer|min:0',
            'language' => 'nullable|string|max:10',
            'cover_url' => 'nullable|url',
            'thumbnail' => 'nullable|url',
            'status' => 'required|in:want_to_read,currently_reading,read',
        ]);

        $book = auth()->user()->books()->create($validated);

        return redirect()->route('books.show', $book)
            ->with('success', 'Book added successfully!');
    }

    public function show(Book $book): View
    {
        $this->authorize('view', $book);

        return view('books.show', compact('book'));
    }

    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        return view('books.edit', compact('book'));
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'isbn13' => 'nullable|string|max:20',
            'publisher' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
            'description' => 'nullable|string',
            'page_count' => 'nullable|integer|min:0',
            'language' => 'nullable|string|max:10',
            'cover_url' => 'nullable|url',
            'thumbnail' => 'nullable|url',
            'status' => 'required|in:want_to_read,currently_reading,read',
            'current_page' => 'nullable|integer|min:0',
        ]);

        $book->update($validated);

        return redirect()->route('books.show', $book)
            ->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully!');
    }

    public function updateProgress(Request $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validate([
            'current_page' => 'required|integer|min:0',
        ]);

        $book->update($validated);

        return back()->with('success', 'Progress updated!');
    }

    public function updateStatus(Request $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validate([
            'status' => 'required|in:want_to_read,currently_reading,read',
        ]);

        $book->update($validated);

        if ($validated['status'] === 'currently_reading' && !$book->started_at) {
            $book->markAsStarted();
        } elseif ($validated['status'] === 'read' && !$book->finished_at) {
            $book->markAsFinished();
        }

        return back()->with('success', 'Status updated!');
    }
}
