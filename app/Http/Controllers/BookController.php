<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\CoverImageService;
use App\Services\GoogleBooksService;
use App\Services\OpenLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // Sorting
        $sort = $request->get('sort', 'added_at_desc');

        // For "ALL" status, prioritize currently_reading books
        if (!$request->has('status')) {
            switch ($sort) {
                case 'title_asc':
                    $query->orderByRaw("CASE WHEN status = 'currently_reading' THEN 0 ELSE 1 END")
                          ->orderBy('title', 'asc');
                    break;
                case 'title_desc':
                    $query->orderByRaw("CASE WHEN status = 'currently_reading' THEN 0 ELSE 1 END")
                          ->orderBy('title', 'desc');
                    break;
                case 'author_asc':
                    $query->orderByRaw("CASE WHEN status = 'currently_reading' THEN 0 ELSE 1 END")
                          ->orderBy('author', 'asc')
                          ->orderBy('title', 'asc');
                    break;
                case 'author_desc':
                    $query->orderByRaw("CASE WHEN status = 'currently_reading' THEN 0 ELSE 1 END")
                          ->orderBy('author', 'desc')
                          ->orderBy('title', 'asc');
                    break;
                case 'published_date_asc':
                    $query->orderByRaw("CASE WHEN status = 'currently_reading' THEN 0 ELSE 1 END")
                          ->orderBy('published_date', 'asc');
                    break;
                case 'published_date_desc':
                    $query->orderByRaw("CASE WHEN status = 'currently_reading' THEN 0 ELSE 1 END")
                          ->orderBy('published_date', 'desc');
                    break;
                case 'added_at_asc':
                    $query->orderByRaw("CASE WHEN status = 'currently_reading' THEN 0 ELSE 1 END")
                          ->orderBy('added_at', 'asc');
                    break;
                case 'added_at_desc':
                default:
                    $query->orderByRaw("CASE WHEN status = 'currently_reading' THEN 0 ELSE 1 END")
                          ->orderBy('added_at', 'desc');
                    break;
            }
        } else {
            // When filtering by status, no priority needed
            switch ($sort) {
                case 'title_asc':
                    $query->orderBy('title', 'asc');
                    break;
                case 'title_desc':
                    $query->orderBy('title', 'desc');
                    break;
                case 'author_asc':
                    $query->orderBy('author', 'asc')->orderBy('title', 'asc');
                    break;
                case 'author_desc':
                    $query->orderBy('author', 'desc')->orderBy('title', 'asc');
                    break;
                case 'published_date_asc':
                    $query->orderBy('published_date', 'asc');
                    break;
                case 'published_date_desc':
                    $query->orderBy('published_date', 'desc');
                    break;
                case 'added_at_asc':
                    $query->orderBy('added_at', 'asc');
                    break;
                case 'added_at_desc':
                default:
                    $query->orderBy('added_at', 'desc');
                    break;
            }
        }

        $books = $query->with('tags')
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
        $provider = $request->get('provider', 'openlibrary'); // Default to OpenLibrary
        $searchResults = [];
        $noResults = false;

        if ($searchQuery) {
            $googleResults = [];
            $openLibraryResults = [];

            // Search based on selected provider
            if ($provider === 'both' || $provider === 'google') {
                $googleResults = $googleBooks->search($searchQuery, 10, $language);
            }

            if ($provider === 'both' || $provider === 'openlibrary') {
                $openLibraryResults = $openLibrary->search($searchQuery, 10, $language);
            }

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

        // Get the cover URL from the API
        $coverUrl = $bookData['cover_url'];

        // If no cover from API, try OpenLibrary fallback
        if (!$coverUrl && ($bookData['isbn13'] || $bookData['isbn'])) {
            $isbn = str_replace(['-', ' '], '', $bookData['isbn13'] ?? $bookData['isbn']);
            $coverUrl = "https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg";
        }

        // Download and store cover image locally
        $localCoverPath = null;
        if ($coverUrl) {
            $identifier = $bookData['isbn13'] ?? $bookData['isbn'] ?? $externalId;
            $localCoverPath = $coverService->downloadAndStore($coverUrl, $identifier);
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
            'cover_url' => $coverUrl,
            'thumbnail' => $coverUrl,
            'local_cover_path' => $localCoverPath,
            'status' => $validated['status'],
            'api_source' => $validated['source'],
            'external_id' => $externalId,
            'added_at' => now(),
        ]);

        // Create BookCover entry if we have a local cover
        if ($localCoverPath) {
            $book->covers()->create([
                'path' => $localCoverPath,
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

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

        $book->load('progressHistory');

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
            'series' => 'nullable|string|max:255',
            'series_position' => 'nullable|integer|min:1',
            'isbn' => 'nullable|string|max:20',
            'isbn13' => 'nullable|string|max:20',
            'publisher' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
            'description' => 'nullable|string',
            'page_count' => 'nullable|integer|min:0',
            'language' => 'nullable|string|max:10',
            'cover_url' => 'nullable|url',
            'thumbnail' => 'nullable|url',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:25600', // 25MB max
            'status' => 'required|in:want_to_read,currently_reading,read',
            'format' => 'nullable|in:digital,paperback,hardcover,audiobook,magazine,spiral_bound,leather_bound,journal,comic,graphic_novel,manga,box_set,omnibus,reference,other',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_currency' => 'nullable|string|size:3',
            'current_page' => 'nullable|integer|min:0',
            'started_at' => 'nullable|date',
            'finished_at' => 'nullable|date',
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');

            // Generate filename based on ISBN or book ID
            $identifier = $book->isbn13 ?? $book->isbn ?? $book->id;
            $extension = $file->getClientOriginalExtension();
            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $identifier) . '_' . time() . '.' . $extension;

            // Store the file
            $path = $file->storeAs('covers', $filename, 'public');

            // Delete old cover if exists
            if ($book->local_cover_path && $book->local_cover_path !== $path) {
                $coverService->delete($book->local_cover_path);
            }

            $validated['local_cover_path'] = $path;
        }

        // Remove cover_image from validated data as it's not a database field
        unset($validated['cover_image']);

        $book->update($validated);

        return redirect()->route('books.show', $book)
            ->with('success', 'Book updated successfully!');
    }

    public function deleteCover(Book $book, CoverImageService $coverService): RedirectResponse
    {
        \Log::info('=== DELETE COVER METHOD CALLED ===', [
            'book_id' => $book->id,
            'user_id' => auth()->id(),
            'request_url' => request()->url(),
            'request_method' => request()->method(),
            'route_name' => request()->route()->getName(),
        ]);

        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete local cover file if exists
        if ($book->local_cover_path) {
            $coverService->delete($book->local_cover_path);
        }

        // Clear cover URLs and local path
        $book->update([
            'cover_url' => null,
            'thumbnail' => null,
            'local_cover_path' => null,
        ]);

        return back()->with('success', 'Cover deleted successfully!');
    }

    public function destroy(Book $book): RedirectResponse
    {
        \Log::info('=== DESTROY METHOD CALLED (DELETE BOOK) ===', [
            'book_id' => $book->id,
            'user_id' => auth()->id(),
            'request_url' => request()->url(),
            'request_method' => request()->method(),
            'route_name' => request()->route()->getName(),
        ]);

        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
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

        // Save to history before updating
        $book->progressHistory()->create([
            'page_number' => $validated['current_page'],
            'recorded_at' => now(),
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
        } elseif ($validated['status'] === 'want_to_read') {
            // Clear started_at when changing back to want_to_read
            $book->update(['started_at' => null]);
        }

        return back()->with('success', 'Status updated!');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'book_ids' => 'required|array',
            'book_ids.*' => 'integer|exists:books,id',
        ]);

        // Delete the books
        $deletedCount = auth()->user()->books()
            ->whereIn('id', $validated['book_ids'])
            ->delete();

        return redirect()->route('books.index')
            ->with('success', "{$deletedCount} book(s) deleted successfully!");
    }

    public function updateFromUrl(Request $request, Book $book, OpenLibraryService $openLibrary, GoogleBooksService $googleBooks, CoverImageService $coverService): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'source' => 'required|string|in:openlibrary,googlebooks,amazon',
            'url' => 'required|string',
        ]);

        $source = $validated['source'];
        $url = $validated['url'];
        $editionData = null;

        try {
            switch ($source) {
                case 'openlibrary':
                    $editionData = $this->fetchFromOpenLibrary($url, $openLibrary);
                    break;

                case 'googlebooks':
                    $editionData = $this->fetchFromGoogleBooks($url, $googleBooks);
                    break;

                case 'amazon':
                    $editionData = $this->fetchFromAmazon($url);
                    break;
            }

            if (!$editionData) {
                return back()->with('error', "Could not fetch book data from {$source}. Please check the URL/ID and try again.");
            }

            // Download and store cover image locally if available
            if (!empty($editionData['cover_url'])) {
                $identifier = $editionData['isbn13'] ?? $editionData['isbn'] ?? $book->id;
                $localCoverPath = $coverService->downloadAndStore($editionData['cover_url'], $identifier);

                if ($localCoverPath) {
                    // Delete old cover if exists
                    if ($book->local_cover_path) {
                        $coverService->delete($book->local_cover_path);
                    }

                    $editionData['local_cover_path'] = $localCoverPath;

                    // Create BookCover entry
                    $book->covers()->create([
                        'path' => $localCoverPath,
                        'is_primary' => $book->covers()->count() === 0,
                        'sort_order' => $book->covers()->count(),
                    ]);
                }
            }

            // Update the book with edition data
            $book->update($editionData);

            return redirect()->route('books.show', $book)
                ->with('success', 'Book updated successfully from ' . ucfirst($source) . '!');

        } catch (\Exception $e) {
            Log::error('Error updating book from URL', [
                'source' => $source,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'An error occurred while updating the book. Please try again.');
        }
    }

    protected function fetchFromOpenLibrary(string $url, OpenLibraryService $openLibrary): ?array
    {
        $editionData = $openLibrary->getEdition($url);

        if (!$editionData) {
            return null;
        }

        return [
            'title' => $editionData['title'] ?? null,
            'author' => $editionData['author'] ?? null,
            'isbn' => $editionData['isbn'] ?? null,
            'isbn13' => $editionData['isbn13'] ?? null,
            'publisher' => $editionData['publisher'] ?? null,
            'published_date' => $editionData['published_date'] ?? null,
            'page_count' => $editionData['page_count'] ?? null,
            'language' => $editionData['language'] ?? null,
            'cover_url' => $editionData['cover_url'] ?? null,
            'thumbnail' => $editionData['thumbnail'] ?? null,
            'openlibrary_edition_id' => $editionData['openlibrary_edition_id'] ?? null,
            'openlibrary_url' => $editionData['openlibrary_url'] ?? null,
            'goodreads_id' => $editionData['goodreads_id'] ?? null,
            'librarything_id' => $editionData['librarything_id'] ?? null,
        ];
    }

    protected function fetchFromGoogleBooks(string $url, GoogleBooksService $googleBooks): ?array
    {
        // Extract Volume ID from URL or use directly if it's just an ID
        $volumeId = $this->extractGoogleBooksId($url);

        if (!$volumeId) {
            return null;
        }

        $bookData = $googleBooks->getBook($volumeId);

        if (!$bookData) {
            return null;
        }

        return [
            'title' => $bookData['title'] ?? null,
            'author' => $bookData['author'] ?? null,
            'isbn' => $bookData['isbn'] ?? null,
            'isbn13' => $bookData['isbn13'] ?? null,
            'publisher' => $bookData['publisher'] ?? null,
            'published_date' => $bookData['published_date'] ?? null,
            'description' => $bookData['description'] ?? null,
            'page_count' => $bookData['page_count'] ?? null,
            'language' => $bookData['language'] ?? null,
            'cover_url' => $bookData['cover_url'] ?? null,
            'thumbnail' => $bookData['thumbnail'] ?? null,
        ];
    }

    protected function fetchFromAmazon(string $url): ?array
    {
        // Extract ASIN or ISBN from Amazon URL
        $identifier = $this->extractAmazonIdentifier($url);

        if (!$identifier) {
            return null;
        }

        // Amazon doesn't have a free public API, so we'll use the identifier as ISBN
        // and try to fetch from Google Books or OpenLibrary as a fallback
        // For now, we'll return a basic update with just the ISBN
        return [
            'isbn' => strlen($identifier) === 10 ? $identifier : null,
            'isbn13' => strlen($identifier) === 13 ? $identifier : null,
        ];
    }

    protected function extractGoogleBooksId(string $input): ?string
    {
        // If it's a URL, extract the volume ID
        if (preg_match('/[?&]id=([^&]+)/', $input, $matches)) {
            return $matches[1];
        }

        // If it's a direct books.google.com URL with /books/about/
        if (preg_match('/books\.google\.[^\/]+\/books\/about\/[^\/]+\/([^?]+)/', $input, $matches)) {
            return $matches[1];
        }

        // Otherwise, treat it as a direct volume ID
        // Google Books IDs are typically 12 characters, alphanumeric
        if (preg_match('/^[a-zA-Z0-9_-]{10,14}$/', $input)) {
            return $input;
        }

        return null;
    }

    protected function extractAmazonIdentifier(string $input): ?string
    {
        // Extract ASIN/ISBN from Amazon URL patterns
        // Pattern 1: /dp/ASIN
        if (preg_match('/\/dp\/([A-Z0-9]{10})/', $input, $matches)) {
            return $matches[1];
        }

        // Pattern 2: /gp/product/ASIN
        if (preg_match('/\/gp\/product\/([A-Z0-9]{10})/', $input, $matches)) {
            return $matches[1];
        }

        // Pattern 3: /product/ASIN
        if (preg_match('/\/product\/([A-Z0-9]{10})/', $input, $matches)) {
            return $matches[1];
        }

        // If it's not a URL, treat it as direct ASIN/ISBN (10 or 13 digits/chars)
        if (preg_match('/^[A-Z0-9]{10}$|^\d{13}$/', $input)) {
            return $input;
        }

        return null;
    }

    public function showSeries(string $series): View
    {
        $books = auth()->user()->books()
            ->where('series', $series)
            ->orderBy('series_position')
            ->orderBy('title')
            ->get();

        if ($books->isEmpty()) {
            abort(404, 'Series not found in your library.');
        }

        return view('books.series', [
            'series' => $series,
            'books' => $books,
        ]);
    }

    public function deleteProgressEntry(Book $book, $entryId): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $entry = $book->progressHistory()->findOrFail($entryId);
        $entry->delete();

        // Update current_page to the last entry in history
        $lastEntry = $book->progressHistory()->first();
        if ($lastEntry) {
            $book->update(['current_page' => $lastEntry->page_number]);
        } else {
            $book->update(['current_page' => 0]);
        }

        return back()->with('success', 'Progress entry deleted!');
    }

    public function uploadCover(Request $request, Book $book, CoverImageService $coverService): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'cover_images' => 'required|array|max:10',
            'cover_images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:25600', // 25MB each
        ]);

        $uploadedCount = 0;

        foreach ($request->file('cover_images') as $file) {
            // Generate filename
            $identifier = $book->isbn13 ?? $book->isbn ?? $book->id;
            $extension = $file->getClientOriginalExtension();
            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $identifier) . '_' . time() . '_' . uniqid() . '.' . $extension;

            // Store the file
            $path = $file->storeAs('covers', $filename, 'public');

            // Create BookCover record
            $book->covers()->create([
                'path' => $path,
                'is_primary' => $book->covers()->count() === 0, // First cover is primary
                'sort_order' => $book->covers()->count(),
            ]);

            $uploadedCount++;
        }

        return back()->with('success', $uploadedCount . ' cover(s) uploaded successfully!');
    }

    public function deleteSingleCover(Book $book, $coverId, CoverImageService $coverService): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $cover = $book->covers()->findOrFail($coverId);

        // Delete the file
        $coverService->delete($cover->path);

        // If this was the primary cover, set another one as primary
        $wasPrimary = $cover->is_primary;
        $cover->delete();

        if ($wasPrimary && $book->covers()->count() > 0) {
            $book->covers()->first()->update(['is_primary' => true]);
        }

        return back()->with('success', 'Cover deleted successfully!');
    }

    public function setPrimaryCover(Book $book, $coverId): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Remove primary status from all covers
        $book->covers()->update(['is_primary' => false]);

        // Set the new primary
        $cover = $book->covers()->findOrFail($coverId);
        $cover->update(['is_primary' => true]);

        return back()->with('success', 'Primary cover updated!');
    }

}
