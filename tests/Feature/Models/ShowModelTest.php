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
    expect(Show::isUnguarded())
        ->toBeTrue();
});

it('casts dates', function () {
    // Arrange
    $show = Show::factory()->create();

    // Act
    $casts = $show->getCasts();

    // Assert
    expect($casts)
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

    // Act
    $seasons = $show->seasons;

    // Assert
    expect($seasons)
        ->toBeInstanceOf(Collection::class)
        ->and($seasons->first())->toBeInstanceOf(Season::class)
        ->and($seasons->count())->toBe(3);
});

it('has many episodes thru seasons', function () {
    // Arrange
    $show = Show::factory()
        ->has(
            Season::factory()
                ->has(Episode::factory()->count(3))
        )
        ->create();

    // Act
    $episodes = $show->episodes;

    // Assert
    expect($episodes)
        ->toBeInstanceOf(Collection::class)
        ->and($episodes->first())->toBeInstanceOf(Episode::class)
        ->and($episodes->count())->toBe(3);
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
        'type' => ImageType::TV_BANNER->value,
    ]);

    // Act
    $images = $show->images;

    // Assert
    expect($images)
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
        'type' => ImageType::TV_BANNER->value,
        'likes' => 15,
    ]);

    // Act
    $poster = $show->images()->ofType(ImageType::TV_POSTER)->popular()->first();

    // Assert
    expect($poster->id)->toBe($mostPopular->id);
});

it('has mostPopularImage relationship for each type', function (ImageType $imageType) {
    // Arrange
    $show = Show::factory()->create();

    $lessPopular = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'type' => $imageType->value,
        'likes' => 5,
        'external_id' => '2',
    ]);

    $mostPopular = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'type' => $imageType->value,
        'likes' => 10,
        'external_id' => '1',
    ]);

    // Act
    $image = $show->mostPopularImage($imageType);

    // Assert
    expect($image)
        ->toBeInstanceOf(Image::class)
        ->and($image->id)->toBe($mostPopular->id);
})->with('showImageTypes');

it('returns null when no image of type exists', function (ImageType $imageType) {
    // Arrange
    $show = Show::factory()->create();

    // Create an image of a different type
    $differentType = collect(ImageType::showTypes())
        ->filter(fn ($type) => $type !== $imageType)
        ->first();
    
    Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'type' => $differentType->value,
    ]);

    // Act
    $image = $show->mostPopularImage($imageType);

    // Assert
    expect($image)->toBeNull();
})->with('showImageTypes');

it('uses external_id as tiebreaker for mostPopularImage', function (ImageType $imageType) {
    // Arrange
    $show = Show::factory()->create();

    $higherExternalId = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'type' => $imageType->value,
        'likes' => 10,
        'external_id' => '200',
    ]);

    $lowerExternalId = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'type' => $imageType->value,
        'likes' => 10,
        'external_id' => '100',
    ]);

    // Act
    $image = $show->mostPopularImage($imageType);

    // Assert
    expect($image->id)->toBe($lowerExternalId->id);
})->with('showImageTypes');
