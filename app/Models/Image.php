<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    protected $casts = [
        'likes' => 'integer',
    ];

    /**
     * Get the parent imageable model (Show or Season).
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to order by popularity (likes).
     */
    public function scopePopular($query)
    {
        return $query->orderBy('likes', 'desc');
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by language.
     */
    public function scopeOfLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Get the most popular image of a specific type.
     */
    public function scopeMostPopularOfType($query, string $type)
    {
        return $query->ofType($type)->popular()->first();
    }
}
