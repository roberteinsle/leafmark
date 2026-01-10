<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'invited_by',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user who created this invitation
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if invitation is valid
     */
    public function isValid(): bool
    {
        return is_null($this->used_at) && $this->expires_at->isFuture();
    }

    /**
     * Mark invitation as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Generate a new invitation
     */
    public static function create(array $attributes = [])
    {
        $attributes['token'] = $attributes['token'] ?? Str::random(32);
        $attributes['expires_at'] = $attributes['expires_at'] ?? now()->addDays(7);

        return parent::create($attributes);
    }

    /**
     * Find valid invitation by token and email
     */
    public static function findValidByTokenAndEmail(string $token, string $email): ?self
    {
        return static::where('token', $token)
            ->where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
