<?php

namespace App\Models;

use App\Enums\ImageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    protected $casts = [
        'likes' => 'integer',
        'type' => ImageType::class,
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
     * Scope a query to get the most popular (likes with external_id tiebreaker).
     */
    public function scopeMostPopular($query)
    {
        return $query->orderByDesc('likes')->orderBy('external_id');
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, ImageType $type)
    {
        return $query->where('type', $type->value);
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
    public function scopeMostPopularOfType($query, ImageType $type)
    {
        return $query->ofType($type)->popular()->first();
    }
}
