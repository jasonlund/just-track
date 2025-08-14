<?php

use App\Enums\ImageType;
use App\Models\Episode;
use App\Models\Image;
use App\Models\Season;
use App\Models\Show;
use Illuminate\Database\Eloquent\Collection;

uses()
    ->group('models');

it('is unguarded', function () {
    // Assert
    expect(Season::isUnguarded())
        ->toBeTrue();
});

it('belongs to a show', function () {
    // Arrange
    $show = Show::factory()
        ->create();
    $season = Season::factory(['show_id' => $show->id])
        ->create();

    // Act
    $relatedShow = $season->show;

    // Assert
    expect($relatedShow)
        ->toBeInstanceOf(Show::class);
});

it('has many episodes', function () {
    // Arrange
    $season = Season::factory()->create();
    Episode::factory(['season_id' => $season->id])->count(3)->create();

    // Act
    $episodes = $season->episodes;

    // Assert
    expect($episodes)
        ->toBeInstanceOf(Collection::class)
        ->and($episodes->first())->toBeInstanceOf(Episode::class)
        ->and($episodes->count())->toBe(3);
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
        'type' => ImageType::SEASON_BANNER->value,
    ]);

    // Act
    $images = $season->images;

    // Assert
    expect($images)
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
        'type' => ImageType::SEASON_THUMB->value,
        'likes' => 12,
    ]);

    // Act
    $poster = $season->images()->ofType(ImageType::SEASON_POSTER)->popular()->first();

    // Assert
    expect($poster->id)->toBe($mostPopular->id);
});
