<?php

use App\Enums\ImageType;
use App\Models\Image;
use App\Models\Show;

it('returns hd clear art as first priority for logo', function () {
    // Arrange
    $show = Show::factory()->create();
    $hdClearArt = Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::HD_CLEAR_ART->value,
        'likes' => 10,
    ]);
    Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::HD_TV_LOGO->value,
        'likes' => 20,
    ]);

    // Act
    $logo = $show->logo;

    // Assert
    expect($logo)->not->toBeNull()
        ->and($logo->id)->toBe($hdClearArt->id)
        ->and($logo->type)->toBe(ImageType::HD_CLEAR_ART);
});

it('returns clear art when hd clear art is not available', function () {
    // Arrange
    $show = Show::factory()->create();
    $clearArt = Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::CLEAR_ART->value,
        'likes' => 10,
    ]);
    Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::HD_TV_LOGO->value,
        'likes' => 20,
    ]);

    // Act
    $logo = $show->logo;

    // Assert
    expect($logo)->not->toBeNull()
        ->and($logo->id)->toBe($clearArt->id)
        ->and($logo->type)->toBe(ImageType::CLEAR_ART);
});

it('returns hd tv logo when clear art types are not available', function () {
    // Arrange
    $show = Show::factory()->create();
    $hdTvLogo = Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::HD_TV_LOGO->value,
        'likes' => 10,
    ]);
    Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::CLEAR_LOGO->value,
        'likes' => 20,
    ]);

    // Act
    $logo = $show->logo;

    // Assert
    expect($logo)->not->toBeNull()
        ->and($logo->id)->toBe($hdTvLogo->id)
        ->and($logo->type)->toBe(ImageType::HD_TV_LOGO);
});

it('returns clear logo when higher priority types are not available', function () {
    // Arrange
    $show = Show::factory()->create();
    $clearLogo = Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::CLEAR_LOGO->value,
        'likes' => 10,
    ]);
    Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::TV_THUMB->value,
        'likes' => 20,
    ]);

    // Act
    $logo = $show->logo;

    // Assert
    expect($logo)->not->toBeNull()
        ->and($logo->id)->toBe($clearLogo->id)
        ->and($logo->type)->toBe(ImageType::CLEAR_LOGO);
});

it('returns tv thumb as last resort', function () {
    // Arrange
    $show = Show::factory()->create();
    $tvThumb = Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::TV_THUMB->value,
        'likes' => 10,
    ]);

    // Act
    $logo = $show->logo;

    // Assert
    expect($logo)->not->toBeNull()
        ->and($logo->id)->toBe($tvThumb->id)
        ->and($logo->type)->toBe(ImageType::TV_THUMB);
});

it('returns null when no logo images are available', function () {
    // Arrange
    $show = Show::factory()->create();
    Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::TV_POSTER->value,
        'likes' => 10,
    ]);
    Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::TV_BANNER->value,
        'likes' => 10,
    ]);

    // Act
    $logo = $show->logo;

    // Assert
    expect($logo)->toBeNull();
});

it('returns the most popular image when multiple of same type exist', function () {
    // Arrange
    $show = Show::factory()->create();
    $popularLogo = Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::HD_CLEAR_ART->value,
        'likes' => 100,
    ]);
    Image::factory()->create([
        'imageable_id' => $show->id,
        'imageable_type' => Show::class,
        'type' => ImageType::HD_CLEAR_ART->value,
        'likes' => 50,
    ]);

    // Act
    $logo = $show->logo;

    // Assert
    expect($logo)->not->toBeNull()
        ->and($logo->id)->toBe($popularLogo->id)
        ->and($logo->likes)->toBe(100);
});