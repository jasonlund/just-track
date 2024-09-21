<?php

use App\Models\Season;
use App\Models\Show;
use App\Models\Episode;
use Illuminate\Database\Eloquent\Collection;

uses()
    ->group('models');

it("belongs to a show", function() {
    // Arrange
    $show = Show::factory()
        ->create();
    $season = Season::factory(['show_id' => $show->id])
        ->create();

    // Act & Assert
    expect($season->show)
        ->toBeInstanceOf(Show::class);
});

it("has many episodes", function() {
    // Arrange
    $season = Season::factory()->create();
    Episode::factory(['season_id' => $season->id])->count(3)->create();

    // Act & Assert
    expect($season->episodes)
        ->toBeInstanceOf(Collection::class)
        ->first()->toBeInstanceOf(Episode::class)

        ->and($season->episodes->count())
        ->toBe(3);
});
