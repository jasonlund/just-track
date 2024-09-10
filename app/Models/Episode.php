<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id', 'show_id', 'number', 'absolute_number', 'season', 'name', 'aired', 'runtime', 'overview'
    ];

    /**
     * Scope a query to only return episodes "critical" to the story.
     * That is, they have an absolute number.
     */
    public function scopeCritical(Builder $query): void
    {
//        $query->whereNot('absolute_number', 0)
//            ->orderBy('absolute_number');
    }
}
