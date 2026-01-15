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
     * Get all available columns for table view
     */
    public static function getAllAvailableColumns(): array
    {
        return [
            'cover',
            'title',
            'author',
            'series',
            'status',
            'rating',
            'pages',
            'current_page',
            'language',
            'publisher',
            'published_date',
            'isbn',
            'format',
            'purchase_date',
            'purchase_price',
            'date_added',
            'date_started',
            'date_finished',
            'tags',
            'actions',
        ];
    }

    /**
     * Get predefined column sets
     */
    public static function getColumnSets(): array
    {
        return [
            'minimal' => [
                'cover',
                'title',
                'author',
                'status',
                'actions',
            ],
            'default' => [
                'cover',
                'title',
                'author',
                'status',
                'rating',
                'pages',
                'date_added',
                'actions',
            ],
            'reading' => [
                'cover',
                'title',
                'author',
                'series',
                'status',
                'current_page',
                'pages',
                'date_started',
                'date_finished',
                'tags',
                'actions',
            ],
            'catalog' => [
                'cover',
                'title',
                'author',
                'series',
                'publisher',
                'published_date',
                'isbn',
                'language',
                'format',
                'tags',
                'actions',
            ],
            'collection' => [
                'cover',
                'title',
                'author',
                'format',
                'purchase_date',
                'purchase_price',
                'rating',
                'date_added',
                'tags',
                'actions',
            ],
            'complete' => [
                'cover',
                'title',
                'author',
                'series',
                'status',
                'rating',
                'pages',
                'current_page',
                'language',
                'publisher',
                'published_date',
                'isbn',
                'format',
                'purchase_date',
                'purchase_price',
                'date_added',
                'date_started',
                'date_finished',
                'tags',
                'actions',
            ],
        ];
    }

    /**
     * Get default visible columns for table view
     */
    public static function getDefaultColumns(): array
    {
        return static::getColumnSets()['default'];
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
