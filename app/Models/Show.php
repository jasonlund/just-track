<?php

namespace App\Models;

use App\Enums\ImageType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Show extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    protected $casts = [
        'premiered' => 'date',
        'ended' => 'date',
        'external_updated_at' => 'datetime',
        'initialized' => 'boolean',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'external_id';
    }

    /**
     * Get the seasons for the show.
     */
    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    /**
     * Get the episodes for the show.
     */
    public function episodes(): HasManyThrough
    {
        return $this->hasManyThrough(Episode::class, Season::class);
    }

    /**
     * Get all of the show's images.
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Scope a query to only include initialized shows.
     */
    public function scopeInitialized($query)
    {
        return $query->where('initialized', true);
    }

    /**
     * Get the most popular image of a specific type
     */
    public function mostPopularImage(\App\Enums\ImageType $type): ?Image
    {
        return $this->images()
            ->where('type', $type->value)
            ->where(function ($query) {
                $query->where('language', 'en')
                      ->orWhereNull('language');
            })
            ->mostPopular()
            ->first();
    }

    protected function attached(): Attribute
    {
        return Attribute::make(
            get: fn () => auth()->user()->shows->find($this->id) !== null,
        );
    }

    protected function logo(): Attribute
    {
        return Attribute::make(
            get: function () {
                $priorityTypes = [
                    ImageType::HD_CLEAR_ART,
                    ImageType::CLEAR_ART,
                    ImageType::HD_TV_LOGO,
                    ImageType::CLEAR_LOGO,
                    ImageType::TV_THUMB,
                ];

                foreach ($priorityTypes as $type) {
                    $image = $this->mostPopularImage($type);
                    if ($image) {
                        return $image;
                    }
                }

                return null;
            }
        );
    }
}
