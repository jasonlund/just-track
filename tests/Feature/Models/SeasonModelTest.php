<?php

use App\Models\Episode;
use App\Models\Image;
use App\Models\Season;
use App\Models\Show;
use Illuminate\Database\Eloquent\Collection;

uses()
    ->group('models');

it('is unguarded', function () {
    expect(Season::isUnguarded())
        ->toBeTrue();
});

it('belongs to a show', function () {
    // Arrange
    $show = Show::factory()
        ->create();
    $season = Season::factory(['show_id' => $show->id])
        ->create();

    // Act & Assert
    expect($season->show)
        ->toBeInstanceOf(Show::class);
});

it('has many episodes', function () {
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

it('has many images through polymorphic relationship', function () {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);

    Image::factory()->count(2)->seasonPoster()->create([
        'imageable_type' => Season::class,
        'imageable_id' => $season->id,
    ]);

    Image::factory()->create([
        'imageable_type' => Season::class,
        'imageable_id' => $season->id,
        'type' => 'seasonbanner',
    ]);

    // Act & Assert
    expect($season->images)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Image::class);
});

it('can get most popular season image', function () {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);

    $lessPopular = Image::factory()->seasonPoster()->create([
        'imageable_type' => Season::class,
        'imageable_id' => $season->id,
        'likes' => 3,
    ]);

    $mostPopular = Image::factory()->seasonPoster()->create([
        'imageable_type' => Season::class,
        'imageable_id' => $season->id,
        'likes' => 8,
    ]);

    Image::factory()->create([
        'imageable_type' => Season::class,
        'imageable_id' => $season->id,
        'type' => 'seasonthumb',
        'likes' => 12,
    ]);

    // Act & Assert
    $poster = $season->images()->ofType('seasonposter')->popular()->first();

    expect($poster->id)->toBe($mostPopular->id);
});
