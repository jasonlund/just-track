<?php

namespace App\Services;

use App\Models\Season;
use App\Models\Show;

class SeasonService
{
    /**
     * Create a season
     */
    public function create(Show|int $show, array $seasonData): Season
    {
        $showId = $show instanceof Show ? $show->id : $show;

        return Season::create([
            'show_id' => $showId,
            'external_id' => $seasonData['id'],
            'number' => $seasonData['number'],
            'name' => $seasonData['name'],
            'image' => $seasonData['image']['original'] ?? null,
        ]);
    }
}
