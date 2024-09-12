<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Show extends Model
{
    use HasFactory;

    protected $fillable = ['external_id', 'name', 'original_name', 'first_air_date', 'overview', 'origin_country'];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'external_id';
    }

    /**
     * Get the episodes for the show.
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'show_id', 'external_id');
    }

    protected function init(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name !== null,
        );
    }
}
