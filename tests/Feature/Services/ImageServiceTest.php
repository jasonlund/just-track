<?php

use App\Enums\ImageType;
use App\Models\Image;
use App\Models\Season;
use App\Models\Show;
use App\Services\FanArtTVService;
use App\Services\ImageService;

beforeEach(function () {
    $this->fanArtTVService = new FanArtTVService;
    $this->imageService = new ImageService($this->fanArtTVService);
});

it('fetches and stores show images from FanArt.tv', function () {
    // Arrange
    $show = Show::factory()->create([
        'external_id' => 210,
        'tvdb_id' => 78804, // Doctor Who
    ]);
    Season::factory()->create(['show_id' => $show->id, 'number' => 1]);
    Season::factory()->create(['show_id' => $show->id, 'number' => 3]);
    $fixture = json_decode(file_get_contents(base_path('tests/Fixtures/Http/FanArtTV/tv-78804.json')), true);
    $expectedCount = collect(ImageType::showTypes())
        ->map(fn ($type) => collect($fixture[$type->value] ?? [])->count())
        ->sum();

    // Act
    $count = $this->imageService->fetchAndStoreShowImages($show);
    $showImages = $show->images()->get();
    $poster = $show->images()->ofType(ImageType::TV_POSTER)->first();

    // Assert
    expect($count)->toBeGreaterThan(0)
        ->and($showImages)->toHaveCount($expectedCount)
        ->and($poster)
        ->toBeInstanceOf(Image::class)
        ->and($poster->external_path)->toStartWith('https://assets.fanart.tv/')
        ->and($poster->language)->toBeIn(['en', 'es', 'fr', 'de', 'ru', '00', null])
        ->and($poster->likes)->toBeGreaterThanOrEqual(0);
});

it('stores season images to correct seasons', function () {
    // Arrange
    $show = Show::factory()->create([
        'external_id' => 210,
        'tvdb_id' => 78804,
    ]);
    $season1 = Season::factory()->create(['show_id' => $show->id, 'number' => 1]);
    $season3 = Season::factory()->create(['show_id' => $show->id, 'number' => 3]);
    $fixture = json_decode(file_get_contents(base_path('tests/Fixtures/Http/FanArtTV/tv-78804.json')), true);
    $season1ExpectedCount = collect($fixture[ImageType::SEASON_POSTER->value] ?? [])
        ->filter(fn ($img) => $img['season'] === '1')
        ->count();
    $season3ExpectedCount = collect($fixture[ImageType::SEASON_POSTER->value] ?? [])
        ->filter(fn ($img) => $img['season'] === '3')
        ->count();

    // Act
    $this->imageService->fetchAndStoreShowImages($show);
    $season1ActualCount = $season1->images()->ofType(ImageType::SEASON_POSTER)->count();
    $season3ActualCount = $season3->images()->ofType(ImageType::SEASON_POSTER)->count();

    // Assert
    if ($season1ExpectedCount > 0) {
        expect($season1ActualCount)->toBe($season1ExpectedCount);
    }
    if ($season3ExpectedCount > 0) {
        expect($season3ActualCount)->toBe($season3ExpectedCount);
    }
});

it('skips shows without TVDB ID', function () {
    // Arrange
    $show = Show::factory()->create([
        'tvdb_id' => null,
    ]);

    // Act
    $count = $this->imageService->fetchAndStoreShowImages($show);
    $imageCount = $show->images()->count();

    // Assert
    expect($count)->toBe(0)
        ->and($imageCount)->toBe(0);
});

it('handles FanArt.tv API returning null', function () {
    // Arrange
    $show = Show::factory()->create([
        'tvdb_id' => 999999,
    ]);

    // Act
    $count = $this->imageService->fetchAndStoreShowImages($show);
    $imageCount = $show->images()->count();

    // Assert
    expect($count)->toBe(0)
        ->and($imageCount)->toBe(0);
});

it('updates existing images instead of duplicating', function () {
    // Arrange
    $show = Show::factory()->create([
        'tvdb_id' => 78804,
    ]);

    // Act
    $count1 = $this->imageService->fetchAndStoreShowImages($show);
    $imageCount1 = Image::count();
    $count2 = $this->imageService->fetchAndStoreShowImages($show);
    $imageCount2 = Image::count();

    // Assert
    expect($imageCount2)->toBe($imageCount1)
        ->and($count1)->toBe($count2);
});

it('skips season images with "all" or null season', function () {
    // Arrange
    $show = Show::factory()->create([
        'tvdb_id' => 78804,
    ]);
    $fixture = json_decode(file_get_contents(base_path('tests/Fixtures/Http/FanArtTV/tv-78804.json')), true);

    // Act
    $this->imageService->fetchAndStoreShowImages($show);

    // Assert
    foreach (ImageType::seasonTypes() as $seasonType) {
        $allSeasonImages = collect($fixture[$seasonType->value] ?? [])
            ->filter(fn ($img) => ($img['season'] ?? null) === 'all' || ($img['season'] ?? null) === null)
            ->count();

        if ($allSeasonImages > 0) {
            $storedImages = $show->images()->ofType($seasonType)->count();
            expect($storedImages)->toBe(0);
        }
    }
});
