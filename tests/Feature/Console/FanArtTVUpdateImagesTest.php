<?php

use App\Models\Season;
use App\Models\Show;

it('fetches images for all initialized shows with TVDB IDs', function () {
    // Arrange
    $showWithTvdb = Show::factory()->create([
        'initialized' => true,
        'tvdb_id' => 78804,
        'name' => 'Doctor Who',
    ]);
    Season::factory()->create(['show_id' => $showWithTvdb->id, 'number' => 1]);

    $showWithoutTvdb = Show::factory()->create([
        'initialized' => true,
        'tvdb_id' => null,
        'name' => 'Show Without TVDB',
    ]);

    $notInitialized = Show::factory()->create([
        'initialized' => false,
        'tvdb_id' => 12345,
        'name' => 'Not Initialized',
    ]);

    // Act
    $this->artisan('fanarttv:update-images')
        ->expectsOutput('Fetching initialized shows with TVDB IDs...')
        ->expectsOutput('Found 1 initialized shows to process.')
        ->expectsOutputToContain('Doctor Who')
        ->expectsOutput('Image update complete!')
        ->assertSuccessful();

    // Assert - Images should be created for the show with TVDB ID
    expect($showWithTvdb->images()->count())->toBeGreaterThan(0)
        ->and($showWithoutTvdb->images()->count())->toBe(0)
        ->and($notInitialized->images()->count())->toBe(0);
});

it('handles no initialized shows gracefully', function () {
    // Arrange - No initialized shows with TVDB IDs

    // Act & Assert
    $this->artisan('fanarttv:update-images')
        ->expectsOutput('Fetching initialized shows with TVDB IDs...')
        ->expectsOutput('No initialized shows with TVDB IDs found.')
        ->assertSuccessful();
});

it('handles API errors gracefully', function () {
    // Arrange
    $show = Show::factory()->create([
        'initialized' => true,
        'tvdb_id' => 999999,
        'name' => 'Unknown Show',
    ]);

    // Act
    $this->artisan('fanarttv:update-images')
        ->expectsOutput('Fetching initialized shows with TVDB IDs...')
        ->expectsOutput('Found 1 initialized shows to process.')
        ->expectsOutput('Image update complete!')
        ->expectsOutput('Processed 0 total images for 1 shows.')
        ->assertSuccessful();

    // Assert
    expect($show->images()->count())->toBe(0);
});

it('processes multiple shows', function () {
    // Arrange
    $show1 = Show::factory()->create([
        'initialized' => true,
        'tvdb_id' => 78804,
        'name' => 'Doctor Who',
    ]);
    Season::factory()->create(['show_id' => $show1->id, 'number' => 1]);

    $show2 = Show::factory()->create([
        'initialized' => true,
        'tvdb_id' => 999999,
        'name' => 'Unknown Show',
    ]);

    // Act
    $this->artisan('fanarttv:update-images')
        ->expectsOutput('Fetching initialized shows with TVDB IDs...')
        ->expectsOutput('Found 2 initialized shows to process.')
        ->expectsOutput('Image update complete!')
        ->assertSuccessful();

    // Assert
    expect($show1->images()->count())->toBeGreaterThan(0)
        ->and($show2->images()->count())->toBe(0);
});
