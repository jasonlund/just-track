<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    public function show()
    {
        return $this->belongsTo(Show::class);
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['name'] !== '' ?
                $this->attributes['name'] :
                'Season '.$this->attributes['number']
        );
    }
}
