<?php

use App\Services\FanArtTVService;

beforeEach(function () {
    $this->service = new FanArtTVService;
});

it('fetches show images successfully', function () {
    $images = $this->service->getShowImages(78804);

    expect($images)
        ->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('thetvdb_id');

    expect($images['name'])->toBe('Doctor Who (2005)');
    expect($images['thetvdb_id'])->toBe('78804');
});

it('returns null for shows not in FanArtTV', function () {
    $images = $this->service->getShowImages(999999);

    expect($images)->toBeNull();
});

it('includes API key in requests', function () {
    config(['services.fanarttv.api_key' => 'test-api-key']);

    Http::fake([
        'https://webservice.fanart.tv/v3/tv/123*' => Http::response(['test' => 'data']),
    ]);

    $service = new FanArtTVService;
    $result = $service->get('tv/123');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api_key=test-api-key');
    });

    expect($result)->toBeArray()->toHaveKey('test');
});

it('includes user agent in requests via global middleware', function () {
    // Note: The user agent is now set globally in AppServiceProvider
    // This test verifies the global configuration is working
    Http::fake([
        'https://webservice.fanart.tv/v3/tv/456*' => Http::response(['test' => 'data']),
    ]);

    $service = new FanArtTVService;
    $result = $service->get('tv/456');

    Http::assertSent(function ($request) {
        // The global middleware sets the user agent from config
        return isset($request->header('User-Agent')[0]) &&
               $request->header('User-Agent')[0] === config('services.user_agent');
    });

    expect($result)->toBeArray()->toHaveKey('test');
});

it('returns artwork types in response', function () {
    $images = $this->service->getShowImages(78804);

    // Check for various artwork types that might be present
    $possibleArtworkTypes = [
        'hdtvlogo', 'clearlogo', 'tvposter', 'tvbanner',
        'hdclearart', 'clearart', 'showbackground', 'tvthumb',
        'seasonposter', 'seasonbanner', 'seasonthumb', 'characterart',
    ];

    $hasArtwork = false;
    foreach ($possibleArtworkTypes as $type) {
        if (isset($images[$type])) {
            $hasArtwork = true;
            expect($images[$type])->toBeArray();

            // Check first item has expected structure
            if (count($images[$type]) > 0) {
                $firstItem = $images[$type][0];
                expect($firstItem)
                    ->toHaveKey('id')
                    ->toHaveKey('url')
                    ->toHaveKey('lang')
                    ->toHaveKey('likes');
            }
        }
    }

    expect($hasArtwork)->toBeTrue();
});

it('handles season-specific artwork correctly', function () {
    $images = $this->service->getShowImages(78804);

    // Check if any season-specific artwork exists
    $seasonArtworkTypes = ['seasonposter', 'seasonbanner', 'seasonthumb'];

    foreach ($seasonArtworkTypes as $type) {
        if (isset($images[$type]) && count($images[$type]) > 0) {
            $firstSeasonArt = $images[$type][0];
            expect($firstSeasonArt)->toHaveKey('season');
            break;
        }
    }
});
