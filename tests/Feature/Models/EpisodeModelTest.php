<?php

use App\Models\Episode;
use App\Models\Season;

uses()
    ->group('models');

it('is unguarded', function () {
    expect(Episode::isUnguarded())
        ->toBeTrue();
});

it('casts dates', function () {
    $episode = Episode::factory()->create();

    expect($episode->getCasts())
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

    // Act & Assert
    expect($episode->season)
        ->toBeInstanceOf(Season::class);
});
