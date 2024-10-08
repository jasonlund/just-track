<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id', 'season_id', 'number', 'production_code', 'name', 'air_date', 'runtime', 'overview',
    ];

    public function season()
    {
        return $this->belongsTo(Season::class);
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
