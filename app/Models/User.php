<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'preferred_language',
        'last_login_at',
        'google_books_api_key',
        'family_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_books_api_key',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class)->ordered();
    }

    public function readingChallenges(): HasMany
    {
        return $this->hasMany(ReadingChallenge::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function ownedFamily(): HasMany
    {
        return $this->hasMany(Family::class, 'owner_id');
    }

    // Helper methods
    public function getDefaultTags(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->tags()->default()->get();
    }

    public function getCustomTags(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->tags()->custom()->get();
    }

    public function getCurrentYearChallenge(): ?ReadingChallenge
    {
        return $this->readingChallenges()->where('year', now()->year)->first();
    }

    /**
     * Check if user is in a family
     */
    public function hasFamily(): bool
    {
        return !is_null($this->family_id);
    }

    /**
     * Check if user owns a family
     */
    public function ownsFamily(): bool
    {
        return Family::where('owner_id', $this->id)->exists();
    }

    /**
     * Leave current family
     */
    public function leaveFamily(): void
    {
        $this->update(['family_id' => null]);
    }
}
