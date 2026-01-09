<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgressHistory extends Model
{
    protected $table = 'reading_progress_history';

    protected $fillable = [
        'book_id',
        'page_number',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'page_number' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
