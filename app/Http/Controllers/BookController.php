<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\GoogleBooksService;
use App\Services\OpenLibraryService;
use App\Services\CoverImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = auth()->user()->books();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('author', 'like', "%{$searchTerm}%")
                  ->orWhere('isbn', 'like', "%{$searchTerm}%")
                  ->orWhere('isbn13', 'like', "%{$searchTerm}%");
            });
        }

        // Author filter
        if ($request->has('author') && $request->author) {
            $query->where('author', $request->author);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $books = $query->with('tags')
            ->orderBy('added_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get counts for each status
        $baseQuery = auth()->user()->books();
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $baseQuery->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('author', 'like', "%{$searchTerm}%")
                  ->orWhere('isbn', 'like', "%{$searchTerm}%")
                  ->orWhere('isbn13', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->has('author') && $request->author) {
            $baseQuery->where('author', $request->author);
        }

        $counts = [
            'all' => (clone $baseQuery)->count(),
            'want_to_read' => (clone $baseQuery)->where('status', 'want_to_read')->count(),
            'currently_reading' => (clone $baseQuery)->where('status', 'currently_reading')->count(),
            'read' => (clone $baseQuery)->where('status', 'read')->count(),
        ];

        return view('books.index', compact('books', 'counts'));
    }

    public function create(Request $request, GoogleBooksService $googleBooks, OpenLibraryService $openLibrary): View
    {
        $searchQuery = $request->get('q');
        $language = $request->get('lang', auth()->user()->preferred_language ?? 'en');
        $searchResults = [];
        $noResults = false;

        if ($searchQuery) {
            // Search both Google Books and Open Library
            $googleResults = $googleBooks->search($searchQuery, 10, $language);
            $openLibraryResults = $openLibrary->search($searchQuery, 10, $language);

            // Merge and deduplicate results based on ISBN
            $searchResults = $this->mergeSearchResults($googleResults, $openLibraryResults);
            $noResults = empty($searchResults);
        }

        return view('books.create', compact('searchQuery', 'searchResults', 'noResults'));
    }

    /**
     * Merge and deduplicate search results from multiple sources
     */
    private function mergeSearchResults(array $googleResults, array $openLibraryResults): array
    {
        $merged = [];
        $seenIsbns = [];

        // Add Google Books results first (they tend to have better data)
        foreach ($googleResults as $result) {
            $isbn = $result['isbn13'] ?? $result['isbn'] ?? null;
            if ($isbn && !in_array($isbn, $seenIsbns)) {
                $seenIsbns[] = $isbn;
                $result['source'] = 'google';
                $merged[] = $result;
            } elseif (!$isbn) {
                // Add books without ISBN too
                $result['source'] = 'google';
                $merged[] = $result;
            }
        }

        // Add Open Library results that aren't duplicates
        foreach ($openLibraryResults as $result) {
            $isbn = $result['isbn13'] ?? $result['isbn'] ?? null;
            if ($isbn && !in_array($isbn, $seenIsbns)) {
                $seenIsbns[] = $isbn;
                $result['source'] = 'openlibrary';
                $merged[] = $result;
            } elseif (!$isbn) {
                // Add books without ISBN too
                $result['source'] = 'openlibrary';
                $merged[] = $result;
            }
        }

        return $merged;
    }

    public function storeFromApi(Request $request, GoogleBooksService $googleBooks, OpenLibraryService $openLibrary, CoverImageService $coverService): RedirectResponse
    {
        $validated = $request->validate([
            'google_books_id' => 'nullable|string',
            'open_library_id' => 'nullable|string',
            'source' => 'required|in:google,openlibrary',
            'status' => 'required|in:want_to_read,currently_reading,read',
        ]);

        // Fetch book data from appropriate source
        if ($validated['source'] === 'google') {
            $bookData = $googleBooks->getBook($validated['google_books_id']);
            $externalId = $validated['google_books_id'];
        } else {
            $bookData = $openLibrary->getBook($validated['open_library_id']);
            $externalId = $validated['open_library_id'];
        }

        if (!$bookData) {
            return back()->with('error', 'Could not fetch book data from API.');
        }

        // Try to download and store cover image locally
        $coverUrl = $bookData['cover_url'];
        $localCoverPath = null;

        // If no cover from API, try OpenLibrary fallback
        if (!$coverUrl && ($bookData['isbn13'] || $bookData['isbn'])) {
            $coverUrl = $coverService->getOpenLibraryCover($bookData['isbn13'] ?? $bookData['isbn']);
        }

        // Download and store the cover locally
        if ($coverUrl) {
            $bookIdentifier = $bookData['isbn13'] ?? $bookData['isbn'] ?? $bookData['title'];
            $localCoverPath = $coverService->storeCoverFromUrl($coverUrl, $bookIdentifier);
        }

        $book = auth()->user()->books()->create([
            'title' => $bookData['title'],
            'author' => $bookData['author'],
            'isbn' => $bookData['isbn'],
            'isbn13' => $bookData['isbn13'],
            'publisher' => $bookData['publisher'],
            'published_date' => $bookData['published_date'],
            'description' => $bookData['description'],
            'page_count' => $bookData['page_count'],
            'language' => $bookData['language'],
            'cover_url' => $bookData['cover_url'],
            'thumbnail' => $bookData['thumbnail'],
            'local_cover_path' => $localCoverPath,
            'status' => $validated['status'],
            'api_source' => $validated['source'],
            'external_id' => $externalId,
            'added_at' => now(),
        ]);

        if ($validated['status'] === 'currently_reading') {
            $book->markAsStarted();
        } elseif ($validated['status'] === 'read') {
            $book->markAsFinished();
        }

        return redirect()->route('books.show', $book)
            ->with('success', 'Book added successfully!');
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

        $validated['added_at'] = now();
        $book = auth()->user()->books()->create($validated);

        return redirect()->route('books.show', $book)
            ->with('success', 'Book added successfully!');
    }

    public function show(Book $book): View
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('books.show', compact('book'));
    }

    public function edit(Book $book): View
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('books.edit', compact('book'));
    }

    public function update(Request $request, Book $book, CoverImageService $coverService): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

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
            'format' => 'nullable|in:digital,paperback,hardcover,audiobook,magazine,spiral_bound,leather_bound,journal,comic,graphic_novel,manga,box_set,omnibus,reference,other',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_currency' => 'nullable|string|size:3',
            'current_page' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old cover if it exists
            if ($book->local_cover_path) {
                $coverService->deleteCover($book->local_cover_path);
            }

            // Upload new cover
            $bookIdentifier = $validated['isbn13'] ?? $validated['isbn'] ?? $validated['title'];
            $localCoverPath = $coverService->storeUploadedCover($request->file('cover_image'), $bookIdentifier);

            if ($localCoverPath) {
                $validated['local_cover_path'] = $localCoverPath;
            }
        }

        $book->update($validated);

        return redirect()->route('books.show', $book)
            ->with('success', 'Book updated successfully!');
    }

    public function deleteCover(Book $book, CoverImageService $coverService): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete local cover file if it exists
        if ($book->local_cover_path) {
            $coverService->deleteCover($book->local_cover_path);
            $book->update(['local_cover_path' => null]);
        }

        return back()->with('success', 'Cover deleted successfully!');
    }

    public function destroy(Book $book, CoverImageService $coverService): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete local cover file if it exists
        if ($book->local_cover_path) {
            $coverService->deleteCover($book->local_cover_path);
        }

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully!');
    }

    public function updateProgress(Request $request, Book $book): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'current_page' => 'required|integer|min:0',
        ]);

        $book->update($validated);

        return back()->with('success', 'Progress updated!');
    }

    public function updateStatus(Request $request, Book $book): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

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

    public function bulkDelete(Request $request, CoverImageService $coverService): RedirectResponse
    {
        $validated = $request->validate([
            'book_ids' => 'required|array',
            'book_ids.*' => 'integer|exists:books,id',
        ]);

        // Get books to delete and remove their cover files
        $books = auth()->user()->books()
            ->whereIn('id', $validated['book_ids'])
            ->get();

        foreach ($books as $book) {
            if ($book->local_cover_path) {
                $coverService->deleteCover($book->local_cover_path);
            }
        }

        // Delete the books
        $deletedCount = auth()->user()->books()
            ->whereIn('id', $validated['book_ids'])
            ->delete();

        return redirect()->route('books.index')
            ->with('success', "{$deletedCount} book(s) deleted successfully!");
    }
}
