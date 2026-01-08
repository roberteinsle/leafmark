<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
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

    // Helper methods
    public function getDefaultTags(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->tags()->default()->get();
    }

    public function getCustomTags(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->tags()->custom()->get();
    }
}
