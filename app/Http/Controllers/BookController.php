<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookViewPreference;
use App\Services\AmazonScraperService;
use App\Services\BigBookApiService;
use App\Services\BookBrainzService;
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
        // Get or create view preference for current shelf
        $shelf = $request->get('status', 'all');
        $viewPref = BookViewPreference::getForUser(auth()->id(), $shelf);

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

        // Sorting - default to finished_at for "read" tab
        $defaultSort = ($request->get('status') === 'read') ? 'finished_at_desc' : 'added_at_desc';
        $sort = $request->get('sort', $defaultSort);

        // Parse sort field and direction
        $sortParts = explode('_', $sort);
        $sortDir = array_pop($sortParts); // Get last part (asc/desc)
        $sortField = implode('_', $sortParts); // Rejoin remaining parts

        // Validate sort direction
        $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'desc';

        // Map sortable fields - allow all table column fields
        $allowedSorts = [
            'title', 'author', 'series', 'rating', 'page_count', 'current_page',
            'publisher', 'published_date', 'purchase_date', 'purchase_price',
            'added_at', 'started_at', 'finished_at'
        ];

        // Default to added_at if field not allowed
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'added_at';
        }

        // For "ALL" status WITHOUT explicit sorting, prioritize currently_reading books
        // But when user explicitly sorts, respect their choice
        if (!$request->has('status') && !$request->has('sort')) {
            // Default: prioritize currently_reading, then sort by added_at
            $query->orderByRaw("CASE WHEN status = 'currently_reading' THEN 0 ELSE 1 END")
                  ->orderBy($sortField, $sortDir);

            // Secondary sort by title
            if ($sortField !== 'title') {
                $query->orderBy('title', 'asc');
            }
        } else {
            // When filtering by status OR explicitly sorting, respect user's choice
            $query->orderBy($sortField, $sortDir);

            // Secondary sort by title for non-title sorts
            if ($sortField !== 'title') {
                $query->orderBy('title', 'asc');
            }
        }

        // Pagination - use per_page from view preference
        $perPage = $request->get('per_page', $viewPref->per_page ?? 25);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 25;

        $books = $query->with('tags', 'covers')
            ->paginate($perPage)
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

        // Get current year's reading challenge
        $currentYear = now()->year;
        $challenge = auth()->user()->readingChallenges()->where('year', $currentYear)->first();

        // Get user's tags for bulk tag operations
        $userTags = auth()->user()->tags()->ordered()->get();

        // Get family members' currently reading books (most recent per member)
        $familyReading = collect();
        if (auth()->user()->hasFamily()) {
            $memberIds = auth()->user()->family->members()
                ->where('users.id', '!=', auth()->id())
                ->pluck('users.id');
            if ($memberIds->isNotEmpty()) {
                $familyReading = Book::whereIn('user_id', $memberIds)
                    ->where('status', 'currently_reading')
                    ->with(['user:id,name', 'covers'])
                    ->orderBy('updated_at', 'desc')
                    ->get()
                    ->groupBy('user_id')
                    ->map(fn ($books) => $books->first())
                    ->values();
            }
        }

        return view('books.index', compact('books', 'counts', 'challenge', 'viewPref', 'userTags', 'familyReading'));
    }

    public function create(Request $request, GoogleBooksService $googleBooks, OpenLibraryService $openLibrary, BookBrainzService $bookBrainz, BigBookApiService $bigBook): View
    {
        $searchQuery = $request->get('q');
        $language = $request->get('lang', auth()->user()->preferred_language ?? 'en');
        $provider = $request->get('provider', 'openlibrary'); // Default to OpenLibrary
        $searchResults = [];
        $noResults = false;

        if ($searchQuery) {
            $googleResults = [];
            $openLibraryResults = [];
            $bookBrainzResults = [];
            $bigBookResults = [];

            // Search based on selected provider
            if ($provider === 'both' || $provider === 'google') {
                $googleResults = $googleBooks->search($searchQuery, 10, $language);
            }

            if ($provider === 'both' || $provider === 'openlibrary') {
                $openLibraryResults = $openLibrary->search($searchQuery, 10, $language);
            }

            if ($provider === 'both' || $provider === 'bookbrainz') {
                $bookBrainzResults = $bookBrainz->search($searchQuery, 10, $language);
            }

            if ($provider === 'both' || $provider === 'bigbook') {
                $bigBookResults = $bigBook->search($searchQuery, 10, $language);
            }

            // Merge and deduplicate results based on ISBN
            $searchResults = $this->mergeSearchResults($googleResults, $openLibraryResults, $bookBrainzResults, $bigBookResults);
            $noResults = empty($searchResults);
        }

        return view('books.create', compact('searchQuery', 'searchResults', 'noResults'));
    }

    /**
     * Merge and deduplicate search results from multiple sources
     */
    private function mergeSearchResults(array $googleResults, array $openLibraryResults, array $bookBrainzResults = [], array $bigBookResults = []): array
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

        // Add Big Book API results
        foreach ($bigBookResults as $result) {
            $isbn = $result['isbn13'] ?? $result['isbn'] ?? null;
            if ($isbn && !in_array($isbn, $seenIsbns)) {
                $seenIsbns[] = $isbn;
                $result['source'] = 'bigbook';
                $merged[] = $result;
            } elseif (!$isbn) {
                // Add books without ISBN too
                $result['source'] = 'bigbook';
                $merged[] = $result;
            }
        }

        // Add BookBrainz results
        foreach ($bookBrainzResults as $result) {
            $isbn = $result['isbn13'] ?? $result['isbn'] ?? null;
            if ($isbn && !in_array($isbn, $seenIsbns)) {
                $seenIsbns[] = $isbn;
                $result['source'] = 'bookbrainz';
                $merged[] = $result;
            } elseif (!$isbn) {
                // Add books without ISBN too
                $result['source'] = 'bookbrainz';
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

    /**
     * Scrape book data from Amazon URL
     */
    public function scrapeAmazon(Request $request, AmazonScraperService $amazonScraper, CoverImageService $coverService): RedirectResponse
    {
        $validated = $request->validate([
            'amazon_url' => 'required|url',
        ]);

        $bookData = $amazonScraper->scrapeFromUrl($validated['amazon_url']);

        if (!$bookData || empty($bookData['title'])) {
            return back()->with('error', __('app.books.amazon_scrape_failed'));
        }

        // Download and store cover image locally
        $localCoverPath = null;
        $coverUrl = $bookData['thumbnail'] ?? null;

        if ($coverUrl) {
            $identifier = $bookData['isbn13'] ?? $bookData['isbn'] ?? 'amazon_' . time();
            $localCoverPath = $coverService->downloadAndStore($coverUrl, $identifier);
        }

        $book = auth()->user()->books()->create([
            'title' => $bookData['title'],
            'author' => $bookData['author'] ?? null,
            'isbn' => $bookData['isbn'] ?? null,
            'isbn13' => $bookData['isbn13'] ?? null,
            'publisher' => $bookData['publisher'] ?? null,
            'published_date' => $bookData['published_date'] ?? null,
            'description' => $bookData['description'] ?? null,
            'page_count' => $bookData['page_count'] ?? null,
            'language' => $bookData['language'] ?? null,
            'status' => 'want_to_read',
            'added_at' => now(),
            'api_source' => 'amazon',
            'external_id' => $amazonScraper->extractIsbnFromUrl($validated['amazon_url']),
            'local_cover_path' => $localCoverPath,
        ]);

        // If we have a local cover, create a BookCover entry
        if ($localCoverPath) {
            $book->covers()->create([
                'path' => $localCoverPath,
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

        return redirect()->route('books.show', $book)->with('success', __('app.books.book_added'));
    }

    public function storeFromApi(Request $request, GoogleBooksService $googleBooks, OpenLibraryService $openLibrary, BookBrainzService $bookBrainz, BigBookApiService $bigBook, CoverImageService $coverService): RedirectResponse
    {
        $validated = $request->validate([
            'google_books_id' => 'nullable|string',
            'open_library_id' => 'nullable|string',
            'bookbrainz_id' => 'nullable|string',
            'bigbook_id' => 'nullable|string',
            'source' => 'required|in:google,openlibrary,bookbrainz,bigbook',
            'status' => 'required|in:want_to_read,currently_reading,read',
        ]);

        // Fetch book data from appropriate source
        if ($validated['source'] === 'google') {
            $bookData = $googleBooks->getBook($validated['google_books_id']);
            $externalId = $validated['google_books_id'];
        } elseif ($validated['source'] === 'bookbrainz') {
            $bookData = $bookBrainz->getBook($validated['bookbrainz_id']);
            $externalId = $validated['bookbrainz_id'];
        } elseif ($validated['source'] === 'bigbook') {
            $bookData = $bigBook->getBook($validated['bigbook_id']);
            $externalId = $validated['bigbook_id'];
        } else {
            $bookData = $openLibrary->getBook($validated['open_library_id']);
            $externalId = $validated['open_library_id'];
        }

        if (!$bookData) {
            return back()->with('error', 'Could not fetch book data from API.');
        }

        // Get the cover URL from the API (with thumbnail fallback)
        $coverUrl = $bookData['cover_url'] ?? $bookData['thumbnail'] ?? null;

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
            'added_at' => 'nullable|date',
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

    public function updateRating(Request $request, Book $book): RedirectResponse
    {
        if ($book->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'rating' => 'nullable|numeric|min:0|max:5',
            'review' => 'nullable|string|max:5000',
        ]);

        $book->update($validated);

        return back()->with('success', 'Rating saved!');
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

    public function bulkAddTags(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'book_ids' => 'required|array',
            'book_ids.*' => 'integer|exists:books,id',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ]);

        $user = auth()->user();

        // Verify all books belong to the user
        $books = $user->books()->whereIn('id', $validated['book_ids'])->get();

        // Verify all tags belong to the user
        $tags = $user->tags()->whereIn('id', $validated['tag_ids'])->get();

        if ($books->count() !== count($validated['book_ids'])) {
            return redirect()->route('books.index')
                ->with('error', 'Some books do not belong to you.');
        }

        if ($tags->count() !== count($validated['tag_ids'])) {
            return redirect()->route('books.index')
                ->with('error', 'Some tags do not belong to you.');
        }

        // Add tags to each book (sync will handle duplicates)
        $totalAdded = 0;
        foreach ($books as $book) {
            $existingTagIds = $book->tags->pluck('id')->toArray();
            $newTagIds = array_diff($validated['tag_ids'], $existingTagIds);

            if (!empty($newTagIds)) {
                $book->tags()->attach($newTagIds);
                $totalAdded += count($newTagIds);
            }
        }

        $tagNames = $tags->pluck('name')->implode(', ');
        return redirect()->route('books.index')
            ->with('success', "Added tag(s) '{$tagNames}' to {$books->count()} book(s) ({$totalAdded} new tag associations).");
    }

    public function bulkRemoveTag(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'book_ids' => 'required|array',
            'book_ids.*' => 'integer|exists:books,id',
            'tag_id' => 'required|integer|exists:tags,id',
        ]);

        $user = auth()->user();

        // Verify all books belong to the user
        $books = $user->books()->whereIn('id', $validated['book_ids'])->get();

        // Verify tag belongs to the user
        $tag = $user->tags()->find($validated['tag_id']);

        if ($books->count() !== count($validated['book_ids'])) {
            return redirect()->route('books.index')
                ->with('error', 'Some books do not belong to you.');
        }

        if (!$tag) {
            return redirect()->route('books.index')
                ->with('error', 'Tag does not belong to you.');
        }

        // Remove tag from each book
        $totalRemoved = 0;
        foreach ($books as $book) {
            if ($book->tags()->where('tags.id', $tag->id)->exists()) {
                $book->tags()->detach($tag->id);
                $totalRemoved++;
            }
        }

        return redirect()->route('books.index')
            ->with('success', "Removed tag '{$tag->name}' from {$totalRemoved} book(s).");
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

    public function toggleViewMode(Request $request): RedirectResponse
    {
        $shelf = $request->get('shelf', 'all');
        $viewMode = $request->get('view_mode', 'card');

        $viewPref = BookViewPreference::getForUser(auth()->id(), $shelf);
        $viewPref->update(['view_mode' => $viewMode]);

        return redirect()->route('books.index', array_filter([
            'status' => $shelf !== 'all' ? $shelf : null,
        ]));
    }

    /**
     * Update column visibility settings
     */
    public function updateColumnSettings(Request $request): RedirectResponse
    {
        $shelf = $request->get('shelf', 'all');
        $visibleColumns = $request->get('visible_columns', []);

        // Always include title and actions (required columns)
        $requiredColumns = ['title', 'actions'];
        $visibleColumns = array_unique(array_merge($requiredColumns, $visibleColumns));

        // Validate that columns are from allowed list
        $allowedColumns = BookViewPreference::getAllAvailableColumns();
        $visibleColumns = array_intersect($visibleColumns, $allowedColumns);

        // Preserve original order from getAllAvailableColumns
        $visibleColumns = array_values(array_intersect($allowedColumns, $visibleColumns));

        $viewPref = BookViewPreference::getForUser(auth()->id(), $shelf);
        $viewPref->update(['visible_columns' => $visibleColumns]);

        return redirect()->route('books.index', array_filter([
            'status' => $shelf !== 'all' ? $shelf : null,
        ]))->with('success', __('app.books.column_settings_saved'));
    }

    /**
     * Fetch fresh data from API for preview
     */
    public function fetchApiData(Request $request, Book $book, GoogleBooksService $googleBooks, OpenLibraryService $openLibrary, BookBrainzService $bookBrainz, BigBookApiService $bigBook)
    {
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $source = $request->get('source', 'auto');

        // Auto-detect source or use specific source
        if ($source === 'auto') {
            // Try to use existing API source if available
            $source = $book->api_source ?? 'google';
        }

        // Determine identifier for API call
        $identifier = null;
        if ($source === 'google') {
            $identifier = $book->external_id ?? ($book->isbn13 ?: $book->isbn);
        } elseif ($source === 'bigbook') {
            $identifier = $book->isbn13 ?: $book->isbn;
        } elseif ($source === 'openlibrary') {
            $identifier = $book->external_id ?? ($book->isbn13 ?: $book->isbn);
        } elseif ($source === 'bookbrainz') {
            $identifier = $book->external_id ?? ($book->isbn13 ?: $book->isbn);
        }

        if (!$identifier) {
            return response()->json(['error' => 'No ISBN or external ID available'], 400);
        }

        // Fetch data from API
        try {
            $bookData = null;
            if ($source === 'google') {
                if ($book->external_id) {
                    $bookData = $googleBooks->getBook($book->external_id);
                } else {
                    $results = $googleBooks->search($identifier);
                    $bookData = !empty($results) ? $results[0] : null;
                }
            } elseif ($source === 'bigbook') {
                if ($book->external_id) {
                    $bookData = $bigBook->getBook($book->external_id);
                } else {
                    $results = $bigBook->search($identifier);
                    $bookData = !empty($results) ? $results[0] : null;
                }
            } elseif ($source === 'openlibrary') {
                if ($book->external_id) {
                    $bookData = $openLibrary->getBook($book->external_id);
                } else {
                    $results = $openLibrary->search($identifier);
                    $bookData = !empty($results) ? $results[0] : null;
                }
            } elseif ($source === 'bookbrainz') {
                if ($book->external_id) {
                    $bookData = $bookBrainz->getBook($book->external_id);
                } else {
                    $results = $bookBrainz->search($identifier);
                    $bookData = !empty($results) ? $results[0] : null;
                }
            }

            if (!$bookData) {
                return response()->json(['error' => 'No data found from API'], 404);
            }

            // Return comparison data
            return response()->json([
                'success' => true,
                'current' => [
                    'title' => $book->title,
                    'author' => $book->author,
                    'description' => $book->description,
                    'page_count' => $book->page_count,
                    'publisher' => $book->publisher,
                    'published_date' => $book->published_date?->format('Y-m-d'),
                    'cover_url' => $book->cover_url,
                    'isbn' => $book->isbn,
                    'isbn13' => $book->isbn13,
                ],
                'fetched' => [
                    'title' => $bookData['title'] ?? null,
                    'author' => $bookData['author'] ?? null,
                    'description' => $bookData['description'] ?? null,
                    'page_count' => $bookData['page_count'] ?? null,
                    'publisher' => $bookData['publisher'] ?? null,
                    'published_date' => $bookData['published_date'] ?? null,
                    'cover_url' => $bookData['cover_url'] ?? null,
                    'isbn' => $bookData['isbn'] ?? null,
                    'isbn13' => $bookData['isbn13'] ?? null,
                ],
                'source' => $source,
            ]);

        } catch (\Exception $e) {
            Log::error('API fetch error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'API request failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Apply selected fields from API data
     */
    public function refreshFromApi(Request $request, Book $book, CoverImageService $coverService)
    {
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'fields' => 'required|array',
            'fields.*' => 'in:title,author,description,page_count,publisher,published_date,cover_url,isbn,isbn13',
            'data' => 'required|array',
            'source' => 'required|string',
        ]);

        $updateData = [];
        $fieldsUpdated = [];

        foreach ($validated['fields'] as $field) {
            if ($field === 'cover_url' && isset($validated['data']['cover_url'])) {
                // Download and update cover
                $coverUrl = $validated['data']['cover_url'];

                // Skip if cover URL is empty or same as current
                if (!$coverUrl || $coverUrl === $book->cover_url) {
                    continue;
                }

                try {
                    // Download cover from URL
                    $identifier = $book->isbn13 ?? $book->isbn ?? $book->id;
                    $localCoverPath = $coverService->downloadAndStore($coverUrl, $identifier);

                    if ($localCoverPath) {
                        // Update book's cover_url reference
                        $updateData['cover_url'] = $coverUrl;
                        $updateData['thumbnail'] = $coverUrl;

                        // Mark all existing covers as non-primary
                        $book->covers()->update(['is_primary' => false]);

                        // Create new BookCover entry as primary
                        $book->covers()->create([
                            'path' => $localCoverPath,
                            'is_primary' => true,
                            'sort_order' => 0,
                        ]);

                        $fieldsUpdated[] = 'cover';
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to download cover from API', [
                        'url' => $coverUrl,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other fields even if cover download fails
                }
            } elseif (isset($validated['data'][$field])) {
                $updateData[$field] = $validated['data'][$field];
                $fieldsUpdated[] = $field;
            }
        }

        if (!empty($updateData)) {
            // Update api_source if refreshing
            $updateData['api_source'] = $validated['source'];
            $book->update($updateData);
        }

        $count = count($fieldsUpdated);
        return redirect()->route('books.edit', $book)
            ->with('success', __('app.books.refreshed_fields', ['count' => $count]));
    }

}
