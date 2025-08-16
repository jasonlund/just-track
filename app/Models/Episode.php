<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    protected $casts = [
        'premiered' => 'date',
        'air_timestamp' => 'datetime',
    ];

    protected $visible = [
        'id',
        'season_id',
        'external_id',
        'number',
        'type',
        'name',
        'premiered',
        'air_timestamp',
        'runtime',
    ];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    protected function number(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['number'] ?? 'S',
        );
    }

    protected function seasonNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->season->number,
        );
    }

    protected function attached(): Attribute
    {
        return Attribute::make(
            get: fn () => auth()->user()->episodes->find($this->id) !== null,
        );
    }
}
