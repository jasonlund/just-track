<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id', 'show_id', 'number', 'absolute_number', 'season', 'name', 'aired', 'runtime', 'overview'
    ];
}
