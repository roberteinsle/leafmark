<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingChallenge extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'goal',
    ];

    protected $casts = [
        'year' => 'integer',
        'goal' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressAttribute(): int
    {
        return $this->user->books()
            ->where('status', 'read')
            ->whereYear('finished_at', $this->year)
            ->count();
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->goal === 0) {
            return 0;
        }
        return min(100, round(($this->progress / $this->goal) * 100, 1));
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->progress >= $this->goal;
    }

    public function getBooksReadThisYearAttribute()
    {
        return $this->user->books()
            ->where('status', 'read')
            ->whereYear('finished_at', $this->year)
            ->orderBy('finished_at', 'desc')
            ->get();
    }
}
