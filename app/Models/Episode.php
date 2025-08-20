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

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function show()
    {
        return $this->hasOneThrough(
            Show::class,
            Season::class,
            'id',
            'id',
            'season_id',
            'show_id',
        );
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('created_at');
    }

    public function scopeWatched($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeUnwatched($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        return $query->whereDoesntHave('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
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

    protected function watchedAt(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! auth()->check()) {
                    return null;
                }

                $userEpisode = $this->users()
                    ->where('user_id', auth()->id())
                    ->first();

                return $userEpisode?->pivot?->created_at;
            }
        );
    }
}
