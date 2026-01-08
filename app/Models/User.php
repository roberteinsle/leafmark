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

    public function shelves(): HasMany
    {
        return $this->hasMany(Shelf::class)->ordered();
    }

    // Helper methods
    public function getDefaultShelves(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->shelves()->default()->get();
    }

    public function getCustomShelves(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->shelves()->custom()->get();
    }
}
