<?php

use App\Enums\ImageType;
use App\Models\Image;
use App\Models\Season;
use App\Models\Show;
use App\Services\FanArtTVService;
use App\Services\ImageService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->fanArtTVService = new FanArtTVService;
    $this->imageService = new ImageService($this->fanArtTVService);
});

it('fetches and stores show images from FanArt.tv', function () {
    // Create a show with TVDB ID matching our fixture
    $show = Show::factory()->create([
        'external_id' => 210,
        'tvdb_id' => 78804, // Doctor Who
    ]);

    // Create seasons for the show
    Season::factory()->create(['show_id' => $show->id, 'number' => 1]);
    Season::factory()->create(['show_id' => $show->id, 'number' => 3]);

    // Fetch and store images
    $count = $this->imageService->fetchAndStoreShowImages($show);

    // Check that images were stored
    expect($count)->toBeGreaterThan(0);

    // Check show images
    $showImages = $show->images()->get();
    $fixture = json_decode(file_get_contents(base_path('tests/Fixtures/Http/FanArtTV/tv-78804.json')), true);

    // Count only show-level images (season images with "all" are now skipped)
    $expectedCount = collect(ImageType::showTypes())
        ->map(fn ($type) => collect($fixture[$type->value] ?? [])->count())
        ->sum();

    expect($showImages)->toHaveCount($expectedCount);

    // Check that images have correct attributes
    $poster = $show->images()->ofType(ImageType::TV_POSTER)->first();
    expect($poster)
        ->toBeInstanceOf(Image::class)
        ->and($poster->external_path)->toStartWith('https://assets.fanart.tv/')
        ->and($poster->language)->toBeIn(['en', 'es', 'fr', 'de', 'ru', '00', null])
        ->and($poster->likes)->toBeGreaterThanOrEqual(0);
});

it('stores season images to correct seasons', function () {
    // Create a show with TVDB ID
    $show = Show::factory()->create([
        'external_id' => 210,
        'tvdb_id' => 78804,
    ]);

    // Create specific seasons
    $season1 = Season::factory()->create(['show_id' => $show->id, 'number' => 1]);
    $season3 = Season::factory()->create(['show_id' => $show->id, 'number' => 3]);

    // Fetch and store images
    $this->imageService->fetchAndStoreShowImages($show);

    // Load fixture to check expected results
    $fixture = json_decode(file_get_contents(base_path('tests/Fixtures/Http/FanArtTV/tv-78804.json')), true);

    // Check season 1 images
    $season1Images = collect($fixture[ImageType::SEASON_POSTER->value] ?? [])
        ->filter(fn ($img) => $img['season'] === '1')
        ->count();

    if ($season1Images > 0) {
        expect($season1->images()->ofType(ImageType::SEASON_POSTER)->count())->toBe($season1Images);
    }

    // Check season 3 images
    $season3Images = collect($fixture[ImageType::SEASON_POSTER->value] ?? [])
        ->filter(fn ($img) => $img['season'] === '3')
        ->count();

    if ($season3Images > 0) {
        expect($season3->images()->ofType(ImageType::SEASON_POSTER)->count())->toBe($season3Images);
    }
});

it('skips shows without TVDB ID', function () {
    $show = Show::factory()->create([
        'tvdb_id' => null,
    ]);

    $count = $this->imageService->fetchAndStoreShowImages($show);

    expect($count)->toBe(0)
        ->and($show->images()->count())->toBe(0);
});

it('handles FanArt.tv API returning null', function () {
    // Mock FanArt.tv to return 404
    Http::fake([
        'https://webservice.fanart.tv/v3/tv/99999*' => Http::response(['status' => 'error', 'error message' => 'Not found'], 404),
    ]);

    $show = Show::factory()->create([
        'tvdb_id' => 99999,
    ]);

    $count = $this->imageService->fetchAndStoreShowImages($show);

    expect($count)->toBe(0)
        ->and($show->images()->count())->toBe(0);
});

it('updates existing images instead of duplicating', function () {
    $show = Show::factory()->create([
        'tvdb_id' => 78804,
    ]);

    // First fetch
    $count1 = $this->imageService->fetchAndStoreShowImages($show);
    $imageCount1 = Image::count();

    // Second fetch should update, not duplicate
    $count2 = $this->imageService->fetchAndStoreShowImages($show);
    $imageCount2 = Image::count();

    expect($imageCount2)->toBe($imageCount1)
        ->and($count1)->toBe($count2);
});

it('skips season images with "all" or null season', function () {
    $show = Show::factory()->create([
        'tvdb_id' => 78804,
    ]);

    // Fetch and store images
    $this->imageService->fetchAndStoreShowImages($show);

    // Load fixture to check for "all" season images
    $fixture = json_decode(file_get_contents(base_path('tests/Fixtures/Http/FanArtTV/tv-78804.json')), true);

    // Check that season type images with "all" season are NOT stored
    foreach (ImageType::seasonTypes() as $seasonType) {
        $allSeasonImages = collect($fixture[$seasonType->value] ?? [])
            ->filter(fn ($img) => ($img['season'] ?? null) === 'all' || ($img['season'] ?? null) === null)
            ->count();

        if ($allSeasonImages > 0) {
            // These should NOT be stored at all
            $storedImages = $show->images()->ofType($seasonType)->count();
            expect($storedImages)->toBe(0);
        }
    }
});
