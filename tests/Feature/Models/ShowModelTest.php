<?php

use App\Models\Season;
use App\Models\Show;
use App\Models\Episode;
use Illuminate\Database\Eloquent\Collection;

uses()
    ->group('models');

it("has many seasons", function() {
    // Arrange
    $show = Show::factory()
        ->has(Season::factory()->count(3))
        ->create();

    // Act & Assert
    expect($show->seasons)
        ->toBeInstanceOf(Collection::class)
        ->first()->toBeInstanceOf(Season::class)

        ->and($show->seasons->count())
        ->toBe(3);
});

it("has many episodes thru seasons", function() {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory(['show_id' => $show->id])->create();
    Episode::factory(['season_id' => $season->id])->count(3)->create();

    // Act & Assert
    expect($show->episodes)
        ->toBeInstanceOf(Collection::class)
        ->first()->toBeInstanceOf(Episode::class)

        ->and($show->episodes->count())
        ->toBe(3);
});
