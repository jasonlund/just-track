<?php

use App\Models\Episode;
use App\Models\Season;

uses()
    ->group('models');

it('is unguarded', function () {
    // Assert
    expect(Episode::isUnguarded())
        ->toBeTrue();
});

it('casts dates', function () {
    // Arrange
    $episode = Episode::factory()->create();

    // Act
    $casts = $episode->getCasts();

    // Assert
    expect($casts)
        ->toEqual([
            'id' => 'int',
            'premiered' => 'date',
            'air_timestamp' => 'datetime',
        ]);
});

it('belongs to a season', function () {
    // Arrange
    $season = Season::factory()
        ->create();
    $episode = Episode::factory(['season_id' => $season->id])
        ->create();

    // Act
    $relatedSeason = $episode->season;

    // Assert
    expect($relatedSeason)
        ->toBeInstanceOf(Season::class);
});
