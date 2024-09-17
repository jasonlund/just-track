<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id', 'season_id', 'number', 'production_code', 'name', 'air_date', 'runtime', 'overview'
    ];
}
