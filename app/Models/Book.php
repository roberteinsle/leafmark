<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'series',
        'series_position',
        'author',
        'isbn',
        'isbn13',
        'publisher',
        'published_date',
        'description',
        'rating',
        'review',
        'page_count',
        'language',
        'cover_url',
        'thumbnail',
        'local_cover_path',
        'current_page',
        'status',
        'format',
        'purchase_date',
        'purchase_price',
        'purchase_currency',
        'added_at',
        'started_at',
        'finished_at',
        'api_source',
        'external_id',
        'openlibrary_edition_id',
        'goodreads_id',
        'librarything_id',
        'openlibrary_url',
    ];

    protected $casts = [
        'published_date' => 'date',
        'purchase_date' => 'date',
        'added_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'page_count' => 'integer',
        'current_page' => 'integer',
        'series_position' => 'integer',
        'purchase_price' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    protected $appends = [
        'cover_image',
        'thumbnail_image',
        'reading_progress',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'book_tag')
            ->withTimestamps()
            ->withPivot('added_at');
    }

    public function covers(): HasMany
    {
        return $this->hasMany(BookCover::class)->ordered();
    }

    public function progressHistory(): HasMany
    {
        return $this->hasMany(ReadingProgressHistory::class)->orderBy('recorded_at', 'desc');
    }

    /**
     * Get the route key - use Unix timestamp from added_at
     */
    public function getRouteKey()
    {
        // Use Unix timestamp as unique identifier
        // Fall back to ID if added_at is not set yet (e.g., during creation)
        return $this->added_at ? $this->added_at->timestamp : $this->id;
    }

    /**
     * Retrieve the model for a bound value using Unix timestamp
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Scope to current user's books for security
        $query = $this->where('user_id', auth()->id());

        // If it's a Unix timestamp, find by added_at
        if (is_numeric($value) && $value > 1000000000) {
            // Convert timestamp to datetime and find the book
            $datetime = \Carbon\Carbon::createFromTimestamp($value);
            $book = $query->where('added_at', $datetime)->first();

            if ($book) {
                return $book;
            }
        }

        // Fallback to ID for backwards compatibility
        if (is_numeric($value)) {
            return $query->where('id', $value)->first();
        }

        return null;
    }

    // Scopes for filtering
    public function scopeWantToRead($query)
    {
        return $query->where('status', 'want_to_read');
    }

    public function scopeCurrentlyReading($query)
    {
        return $query->where('status', 'currently_reading');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    // Helper methods
    public function getReadingProgressAttribute(): int
    {
        if (!$this->page_count || $this->page_count === 0) {
            return 0;
        }

        return (int) round(($this->current_page / $this->page_count) * 100);
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'currently_reading',
            'started_at' => now(),
        ]);
    }

    public function markAsFinished(): void
    {
        $this->update([
            'status' => 'read',
            'finished_at' => now(),
            'current_page' => $this->page_count ?? 0,
        ]);
    }

    /**
     * Get the cover image URL - prefers multiple covers, then local cover, falls back to external URLs
     */
    public function getCoverImageAttribute(): ?string
    {
        // First check for primary cover in covers relationship
        $primaryCover = $this->covers()->primary()->first();
        if ($primaryCover) {
            return $primaryCover->url;
        }

        // Fallback to first cover if no primary set
        $firstCover = $this->covers()->first();
        if ($firstCover) {
            return $firstCover->url;
        }

        // Legacy: Prefer local cover if available
        if ($this->local_cover_path) {
            return asset('storage/' . $this->local_cover_path);
        }

        // Fallback to external URLs
        return $this->cover_url;
    }

    /**
     * Get the thumbnail image URL - prefers multiple covers, then local cover, falls back to external URLs
     */
    public function getThumbnailImageAttribute(): ?string
    {
        // Use same logic as cover_image for consistency
        return $this->cover_image;

        // Legacy: Prefer local cover if available
        if ($this->local_cover_path) {
            return asset('storage/' . $this->local_cover_path);
        }

        // Fallback to external URLs
        return $this->thumbnail ?? $this->cover_url;
    }
}
