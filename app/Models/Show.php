<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Show extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id', 'name', 'original_name', 'status', 'first_air_date', 'overview', 'origin_country'
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

    protected function attached (): Attribute
    {
        return Attribute::make(
            get: fn () => auth()->user()->shows->find($this->id) !== null,
        );
    }

    protected function init(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name !== null,
        );
    }
}
