<?php

use App\Models\Episode;
use App\Models\Season;

uses()
    ->group('models');

it('belongs to a season', function () {
    // Arrange
    $season = Season::factory()
        ->create();
    $episode = Episode::factory(['season_id' => $season->id])
        ->create();

    // Act & Assert
    expect($episode->season)
        ->toBeInstanceOf(Season::class);
});
