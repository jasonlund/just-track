<?php

use App\Models\Show;
use App\Models\Episode;
use Illuminate\Database\Eloquent\Collection;

uses()
    ->group('models');

it("has many episodes", function() {
    // Arrange
    $show = Show::factory()
        ->has(Episode::factory()->count(3))
        ->create();

    // Act & Assert
    expect($show->episodes)
        ->toBeInstanceOf(Collection::class)
        ->first()->toBeInstanceOf(Episode::class)

        ->and($show->episodes->count())
        ->toBe(3);

});
