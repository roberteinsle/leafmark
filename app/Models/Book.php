<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'isbn13',
        'publisher',
        'published_date',
        'description',
        'page_count',
        'language',
        'cover_url',
        'thumbnail',
        'current_page',
        'status',
        'added_at',
        'started_at',
        'finished_at',
        'api_source',
        'external_id',
    ];

    protected $casts = [
        'published_date' => 'date',
        'added_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'page_count' => 'integer',
        'current_page' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shelves(): BelongsToMany
    {
        return $this->belongsToMany(Shelf::class, 'shelf_books')
            ->withTimestamps()
            ->withPivot('added_at');
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
}
