<?php

use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use Illuminate\Database\Eloquent\Collection;

uses()
    ->group('models');

it('is unguarded', function () {
    expect(Show::isUnguarded())
        ->toBeTrue();
});

it('casts dates', function () {
    $show = Show::factory()->create();

    expect($show->getCasts())
        ->toEqual([
            'id' => 'int',
            'premiered' => 'date',
            'external_updated_at' => 'datetime',
        ]);
});

it('has many seasons', function () {
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

it('has many episodes thru seasons', function () {
    // Arrange
    $show = Show::factory()
        ->has(
            Season::factory()
                ->has(Episode::factory()->count(3))
        )
        ->create();

    // Act & Assert
    expect($show->episodes)
        ->toBeInstanceOf(Collection::class)
        ->first()->toBeInstanceOf(Episode::class)

        ->and($show->episodes->count())
        ->toBe(3);
});
