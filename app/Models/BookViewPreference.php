<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookViewPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shelf',
        'view_mode',
        'visible_columns',
        'sort_field',
        'sort_order',
        'per_page',
    ];

    protected $casts = [
        'visible_columns' => 'array',
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default visible columns for table view
     */
    public static function getDefaultColumns(): array
    {
        return [
            'cover',
            'title',
            'author',
            'status',
            'rating',
            'pages',
            'date_added',
            'actions',
        ];
    }

    /**
     * Get or create preference for user and shelf
     */
    public static function getForUser(int $userId, string $shelf = 'all'): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'shelf' => $shelf],
            [
                'view_mode' => 'card',
                'visible_columns' => static::getDefaultColumns(),
                'sort_field' => 'added_at',
                'sort_order' => 'desc',
                'per_page' => 25,
            ]
        );
    }
}
