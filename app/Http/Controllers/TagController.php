<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = auth()->user()->tags;

        return view('tags.index', compact('tags'));
    }

    public function create(): View
    {
        return view('tags.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        auth()->user()->tags()->create($validated);

        return redirect()->route('tags.index')
            ->with('success', 'Tag created successfully!');
    }

    public function show(Tag $tag): View
    {
        if ($tag->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $books = $tag->books()->paginate(20);

        return view('tags.show', compact('tag', 'books'));
    }

    public function edit(Tag $tag): View
    {
        if ($tag->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        if ($tag->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $tag->update($validated);

        return redirect()->route('tags.index')
            ->with('success', 'Tag updated successfully!');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        if ($tag->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($tag->is_default) {
            return back()->with('error', 'Cannot delete default tags.');
        }

        $tag->delete();

        return redirect()->route('tags.index')
            ->with('success', 'Tag deleted successfully!');
    }

    public function addBook(Tag $tag, Book $book): RedirectResponse
    {
        if ($tag->user_id !== auth()->id() || $book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $tag->addBook($book);

        return back()->with('success', 'Book added to tag!');
    }

    public function removeBook(Tag $tag, Book $book): RedirectResponse
    {
        if ($tag->user_id !== auth()->id() || $book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $tag->removeBook($book);

        return back()->with('success', 'Book removed from tag!');
    }
}
