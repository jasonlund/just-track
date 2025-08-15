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

it('has mostPopularImage relationship for each type', function (ImageType $imageType) {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);

    $lessPopular = Image::factory()->create([
        'imageable_type' => Season::class,
        'imageable_id' => $season->id,
        'type' => $imageType->value,
        'likes' => 5,
        'external_id' => '2',
    ]);

    $mostPopular = Image::factory()->create([
        'imageable_type' => Season::class,
        'imageable_id' => $season->id,
        'type' => $imageType->value,
        'likes' => 10,
        'external_id' => '1',
    ]);

    // Act
    $image = $season->mostPopularImage($imageType);

    // Assert
    expect($image)
        ->toBeInstanceOf(Image::class)
        ->and($image->id)->toBe($mostPopular->id);
})->with('seasonImageTypes');

it('returns null when no image of type exists for season', function (ImageType $imageType) {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);

    // Create an image of a different type
    $differentType = collect(ImageType::seasonTypes())
        ->filter(fn ($type) => $type !== $imageType)
        ->first();
    
    Image::factory()->create([
        'imageable_type' => Season::class,
        'imageable_id' => $season->id,
        'type' => $differentType->value,
    ]);

    // Act
    $image = $season->mostPopularImage($imageType);

    // Assert
    expect($image)->toBeNull();
})->with('seasonImageTypes');

it('uses external_id as tiebreaker for season mostPopularImage', function (ImageType $imageType) {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);

    $higherExternalId = Image::factory()->create([
        'imageable_type' => Season::class,
        'imageable_id' => $season->id,
        'type' => $imageType->value,
        'likes' => 10,
        'external_id' => '200',
    ]);

    $lowerExternalId = Image::factory()->create([
        'imageable_type' => Season::class,
        'imageable_id' => $season->id,
        'type' => $imageType->value,
        'likes' => 10,
        'external_id' => '100',
    ]);

    // Act
    $image = $season->mostPopularImage($imageType);

    // Assert
    expect($image->id)->toBe($lowerExternalId->id);
})->with('seasonImageTypes');
