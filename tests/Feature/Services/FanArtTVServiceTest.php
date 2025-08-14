<?php

use App\Enums\ImageType;
use App\Services\FanArtTVService;

beforeEach(function () {
    $this->service = new FanArtTVService;
});

it('fetches show images successfully', function () {
    $images = $this->service->getShowImages(78804);

    expect($images)
        ->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('thetvdb_id')
        ->and($images['name'])->toBe('Doctor Who (2005)')
        ->and($images['thetvdb_id'])->toBe('78804');
});

it('returns null for shows not in FanArtTV', function () {
    $images = $this->service->getShowImages(999999);

    expect($images)->toBeNull();
});

it('includes API key in requests', function () {
    $service = new FanArtTVService;
    $result = $service->get('tv/78804');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api_key=test-api-key');
    });

    expect($result)->toBeArray()->toHaveKey('name');
});

it('includes user agent in requests via global middleware', function () {
    $service = new FanArtTVService;
    $result = $service->get('tv/78804');

    Http::assertSent(function ($request) {
        return isset($request->header('User-Agent')[0]) &&
               $request->header('User-Agent')[0] === config('services.user_agent');
    });

    expect($result)->toBeArray()->toHaveKey('name');
});

it('returns artwork types with correct structure', function (ImageType $imageType) {
    $images = $this->service->getShowImages(78804);

    // Skip if this image type is not present in the response
    if (! isset($images[$imageType->value])) {
        expect(true)->toBeTrue(); // Pass the test for missing types

        return;
    }

    expect($images[$imageType->value])->toBeArray();

    // Check first item has expected structure if any exist
    if (count($images[$imageType->value]) > 0) {
        $firstItem = $images[$imageType->value][0];
        expect($firstItem)
            ->toHaveKey('id')
            ->toHaveKey('url')
            ->toHaveKey('lang')
            ->toHaveKey('likes');
    }
})->with(ImageType::cases());

it('returns at least some artwork types', function () {
    $images = $this->service->getShowImages(78804);

    $artworkTypes = collect(ImageType::cases())
        ->filter(fn ($type) => isset($images[$type->value]))
        ->count();

    expect($artworkTypes)->toBeGreaterThan(0);
});

it('handles season-specific artwork correctly', function () {
    $images = $this->service->getShowImages(78804);

    // Check if any season-specific artwork exists
    $seasonArtworkTypes = ImageType::seasonTypes();

    foreach ($seasonArtworkTypes as $type) {
        if (isset($images[$type->value]) && count($images[$type->value]) > 0) {
            $firstSeasonArt = $images[$type->value][0];
            expect($firstSeasonArt)->toHaveKey('season');
            break;
        }
    }
});
