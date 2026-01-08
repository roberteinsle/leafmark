<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_tag')
            ->withTimestamps()
            ->withPivot('added_at')
            ->orderByPivot('added_at', 'desc');
    }

    // Scopes
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_default', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Helper methods
    public function addBook(Book $book): void
    {
        if (!$this->books()->where('book_id', $book->id)->exists()) {
            $this->books()->attach($book->id, ['added_at' => now()]);
        }
    }

    public function removeBook(Book $book): void
    {
        $this->books()->detach($book->id);
    }

    public function getBooksCountAttribute(): int
    {
        return $this->books()->count();
    }
}
