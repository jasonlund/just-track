<?php

use App\Models\Episode;
use App\Models\Image;
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
            'ended' => 'date',
            'external_updated_at' => 'datetime',
            'initialized' => 'boolean',
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

it('has many images through polymorphic relationship', function () {
    // Arrange
    $show = Show::factory()->create();

    Image::factory()->count(2)->tvPoster()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
    ]);

    Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'type' => 'tvbanner',
    ]);

    // Act & Assert
    expect($show->images)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Image::class);
});

it('can get most popular image of specific type', function () {
    // Arrange
    $show = Show::factory()->create();

    $lessPopular = Image::factory()->tvPoster()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'likes' => 5,
    ]);

    $mostPopular = Image::factory()->tvPoster()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'likes' => 10,
    ]);

    Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'type' => 'tvbanner',
        'likes' => 15,
    ]);

    // Act & Assert
    $poster = $show->images()->ofType('tvposter')->popular()->first();

    expect($poster->id)->toBe($mostPopular->id);
});
