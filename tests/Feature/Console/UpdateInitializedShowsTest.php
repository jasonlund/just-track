<?php

use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use Illuminate\Support\Facades\Http;

it('updates initialized shows with new episodes from fixture', function () {
    // Create an initialized show
    $show = Show::factory()->create([
        'external_id' => 51303,
        'name' => 'Taskmaster NZ',
        'initialized' => true,
    ]);

    $season = Season::factory()->create([
        'show_id' => $show->id,
        'number' => 5,
        'external_id' => 125614,
    ]);

    // Run the command with fixture
    $this->artisan('tvmaze:update-initialized --use-fixture')
        ->expectsOutput('Fetching initialized shows...')
        ->expectsOutput('Found 1 initialized shows to update.')
        ->expectsOutput('Using fixture file...')
        ->expectsOutput('Processing schedule data...')
        ->assertSuccessful();

    // Verify season 6 was created
    $season6 = $show->seasons()->where('number', 6)->first();
    expect($season6)->not->toBeNull();

    // Verify episodes were created
    $episodes = $season6->episodes()->orderBy('number')->get();
    expect($episodes)->toHaveCount(10);

    // Check first episode details
    $firstEpisode = $episodes->first();
    expect($firstEpisode->name)->toBe("It's Like a Make a Wish");
    expect($firstEpisode->number)->toBe(1);
    expect($firstEpisode->premiered->format('Y-m-d'))->toBe('2025-08-18');

    // Verify show was updated
    $show->refresh();
    expect($show->external_updated_at)->not->toBeNull();
});

it('skips non-initialized shows', function () {
    // Create a show that is not initialized
    $show = Show::factory()->create([
        'external_id' => 999999,
        'name' => 'Test Show',
        'initialized' => false,
    ]);

    // Run the command
    $this->artisan('tvmaze:update-initialized --use-fixture')
        ->expectsOutput('Fetching initialized shows...')
        ->expectsOutput('No initialized shows found.')
        ->assertSuccessful();

    // Verify no episodes were created
    expect($show->episodes()->count())->toBe(0);
});

it('updates existing episodes', function () {
    // Create an initialized show with existing episodes
    $show = Show::factory()->create([
        'external_id' => 51303,
        'name' => 'Taskmaster NZ',
        'initialized' => true,
    ]);

    $season = Season::factory()->create([
        'show_id' => $show->id,
        'number' => 6,
    ]);

    // Create an existing episode with old data
    $episode = Episode::factory()->create([
        'season_id' => $season->id,
        'external_id' => 3318266,
        'number' => 1,
        'name' => 'Old Episode Name',
        'premiered' => '2024-01-01',
    ]);

    // Run the command
    $this->artisan('tvmaze:update-initialized --use-fixture')
        ->assertSuccessful();

    // Verify episode was updated
    $episode->refresh();
    expect($episode->name)->toBe("It's Like a Make a Wish");
    expect($episode->premiered->format('Y-m-d'))->toBe('2025-08-18');
});

it('handles API failures gracefully when not using fixture', function () {
    // Create an initialized show
    $show = Show::factory()->create([
        'external_id' => 51303,
        'initialized' => true,
    ]);

    // Mock HTTP to fail
    Http::fake([
        '*' => Http::response(null, 500),
    ]);

    // Run the command
    $this->artisan('tvmaze:update-initialized')
        ->expectsOutput('Fetching initialized shows...')
        ->expectsOutput('Found 1 initialized shows to update.')
        ->expectsOutput('Fetching schedule data from TVMaze...')
        ->expectsOutput('Failed to fetch schedule data from TVMaze.')
        ->assertFailed();
});

it('processes multiple initialized shows', function () {
    // Create multiple initialized shows
    $show1 = Show::factory()->create([
        'external_id' => 51303,
        'initialized' => true,
    ]);

    $show2 = Show::factory()->create([
        'external_id' => 86205,
        'initialized' => true,
    ]); // Another show in the fixture

    // Run the command
    $this->artisan('tvmaze:update-initialized --use-fixture')
        ->expectsOutput('Fetching initialized shows...')
        ->expectsOutput('Found 2 initialized shows to update.')
        ->assertSuccessful();

    // Both shows should be updated
    expect($show1->fresh()->external_updated_at)->not->toBeNull();
    expect($show2->fresh()->external_updated_at)->not->toBeNull();
});
