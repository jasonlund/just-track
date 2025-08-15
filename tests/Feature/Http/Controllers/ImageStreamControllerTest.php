<?php

use App\Services\FanArtTVService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('art');
});

it('serves cached images from the art disk', function () {
    // Arrange
    $path = 'tv/369988/tvthumb/test-image.jpg';
    $imageContent = 'fake image content';
    Storage::disk('art')->put($path, $imageContent);

    // Act
    $response = $this->get('/images/art/' . $path);

    // Assert
    $response->assertOk()
        ->assertHeader('Cache-Control');
});

it('downloads and caches images from FanArtTV when not cached', function () {
    // Arrange
    $path = 'tv/369988/tvthumb/new-image.jpg';
    $imageContent = 'downloaded image content';
    $fanartUrl = FanArtTVService::getAssetBaseUrl() . $path;
    
    Http::fake([
        $fanartUrl => Http::sequence()
            ->push('', 200, ['Content-Type' => 'image/jpeg']) // HEAD request
            ->push($imageContent, 200), // GET request
    ]);

    // Act
    $response = $this->get('/images/art/' . $path);

    // Assert
    $response->assertOk()
        ->assertHeader('Cache-Control');
    
    expect(Storage::disk('art')->exists($path))->toBeTrue()
        ->and(Storage::disk('art')->get($path))->toBe($imageContent);
});

it('returns 404 when image does not exist on FanArtTV', function () {
    // Arrange
    $path = 'tv/999999/tvthumb/non-existent.jpg';
    $fanartUrl = FanArtTVService::getAssetBaseUrl() . $path;
    
    Http::fake([
        $fanartUrl => Http::response('', 404), // HEAD request returns 404
    ]);

    // Act
    $response = $this->get('/images/art/' . $path);

    // Assert
    $response->assertNotFound();
    expect(Storage::disk('art')->exists($path))->toBeFalse();
});

it('returns 404 when FanArtTV GET fails after successful HEAD', function () {
    // Arrange
    $path = 'tv/369988/tvthumb/failing-image.jpg';
    $fanartUrl = FanArtTVService::getAssetBaseUrl() . $path;
    
    Http::fake([
        $fanartUrl => Http::sequence()
            ->push('', 200) // HEAD succeeds
            ->push('', 500), // GET fails
    ]);

    // Act
    $response = $this->get('/images/art/' . $path);

    // Assert
    $response->assertNotFound();
    expect(Storage::disk('art')->exists($path))->toBeFalse();
});

it('serves different image types', function () {
    // Arrange
    $images = [
        'tv/369988/tvthumb/test.jpg',
        'tv/369988/hdtvlogo/test.png',
        'tv/369988/clearart/test.gif',
    ];
    
    foreach ($images as $path) {
        Storage::disk('art')->put($path, 'fake content');
    }

    // Act & Assert
    foreach ($images as $path) {
        $response = $this->get('/images/art/' . $path);
        $response->assertOk()
            ->assertHeader('Cache-Control');
    }
});

it('handles paths with subdirectories correctly', function () {
    // Arrange
    $path = 'tv/78804/seasons/1/seasonposter/season-1.jpg';
    Storage::disk('art')->put($path, 'season poster content');

    // Act
    $response = $this->get('/images/art/' . $path);

    // Assert
    $response->assertOk()
        ->assertHeader('Cache-Control');
});

it('does not re-download already cached images', function () {
    // Arrange
    $path = 'tv/369988/tvthumb/cached-image.jpg';
    $cachedContent = 'already cached content';
    Storage::disk('art')->put($path, $cachedContent);
    
    Http::fake(); // Should not make any requests

    // Act
    $response = $this->get('/images/art/' . $path);

    // Assert
    $response->assertOk();
    Http::assertNothingSent();
    expect(Storage::disk('art')->get($path))->toBe($cachedContent);
});

it('handles special characters in paths', function () {
    // Arrange
    $path = 'tv/369988/tvthumb/image-with-special_chars.jpg';
    Storage::disk('art')->put($path, 'image content');

    // Act
    $response = $this->get('/images/art/' . urlencode($path));

    // Assert
    $response->assertOk();
});