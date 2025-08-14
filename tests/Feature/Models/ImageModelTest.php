<?php

use App\Enums\ImageType;
use App\Models\Image;
use App\Models\Season;
use App\Models\Show;

uses()->group('models');

it('is unguarded', function () {
    expect(Image::isUnguarded())
        ->toBeTrue();
});

it('casts likes to integer and type to enum', function () {
    $show = Show::factory()->create();
    $image = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'likes' => '10',
    ]);

    expect($image->getCasts())
        ->toHaveKey('likes', 'integer')
        ->toHaveKey('type', ImageType::class)
        ->and($image->likes)
        ->toBeInt()
        ->toBe(10)
        ->and($image->type)
        ->toBeInstanceOf(ImageType::class);
});

it('belongs to a show via polymorphic relationship', function () {
    $show = Show::factory()->create();
    $image = Image::factory()
        ->tvPoster()
        ->create([
            'imageable_type' => Show::class,
            'imageable_id' => $show->id,
        ]);

    expect($image->imageable)
        ->toBeInstanceOf(Show::class)
        ->and($image->imageable->id)
        ->toBe($show->id);
});

it('belongs to a season via polymorphic relationship', function () {
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);
    $image = Image::factory()
        ->seasonPoster()
        ->create([
            'imageable_type' => Season::class,
            'imageable_id' => $season->id,
        ]);

    expect($image->imageable)
        ->toBeInstanceOf(Season::class)
        ->and($image->imageable->id)
        ->toBe($season->id);
});

it('can be retrieved from a show', function () {
    $show = Show::factory()->create();
    $images = Image::factory()
        ->count(3)
        ->create([
            'imageable_type' => Show::class,
            'imageable_id' => $show->id,
        ]);

    expect($show->images)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Image::class);
});

it('can be retrieved from a season', function () {
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);
    $images = Image::factory()
        ->count(2)
        ->create([
            'imageable_type' => Season::class,
            'imageable_id' => $season->id,
        ]);

    expect($season->images)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(Image::class);
});

it('can scope by popularity', function () {
    $show = Show::factory()->create();
    $mostPopular = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'likes' => 100,
    ]);
    $leastPopular = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'likes' => 1,
    ]);
    $midPopular = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'likes' => 50,
    ]);

    $images = Image::popular()->get();

    expect($images->first()->id)->toBe($mostPopular->id)
        ->and($images->last()->id)->toBe($leastPopular->id);
});

it('can scope by type', function () {
    $show = Show::factory()->create();
    $poster = Image::factory()->tvPoster()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
    ]);
    $banner = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'type' => ImageType::TV_BANNER->value,
    ]);

    $posters = Image::ofType(ImageType::TV_POSTER)->get();

    expect($posters)
        ->toHaveCount(1)
        ->and($posters->first()->id)
        ->toBe($poster->id);
});

it('can scope by language', function () {
    $show = Show::factory()->create();
    $englishImage = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'language' => 'en',
    ]);
    $spanishImage = Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'language' => 'es',
    ]);

    $englishImages = Image::ofLanguage('en')->get();

    expect($englishImages)
        ->toHaveCount(1)
        ->and($englishImages->first()->id)
        ->toBe($englishImage->id);
});

it('can get most popular image of type', function () {
    $show = Show::factory()->create();
    Image::factory()->tvPoster()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'likes' => 5,
    ]);
    $mostPopularPoster = Image::factory()->tvPoster()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'likes' => 10,
    ]);
    Image::factory()->create([
        'imageable_type' => Show::class,
        'imageable_id' => $show->id,
        'type' => ImageType::TV_BANNER->value,
        'likes' => 20,
    ]);

    $result = Image::mostPopularOfType(ImageType::TV_POSTER);

    expect($result->id)->toBe($mostPopularPoster->id);
});
