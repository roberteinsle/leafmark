<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Family extends Model
{
    protected $fillable = [
        'name',
        'join_code',
        'owner_id',
    ];

    /**
     * Boot the model and generate join code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($family) {
            if (empty($family->join_code)) {
                $family->join_code = static::generateUniqueJoinCode();
            }
        });
    }

    /**
     * Generate a unique join code
     */
    public static function generateUniqueJoinCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('join_code', $code)->exists());

        return $code;
    }

    /**
     * Get the owner of the family
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all members of the family
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'family_id');
    }

    /**
     * Check if a user is the owner
     */
    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Get member count
     */
    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }
}
