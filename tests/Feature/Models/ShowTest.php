<?php

use App\Models\Show;

test('initialized scope returns only initialized shows', function () {
    // Create some shows with different initialized states
    $initializedShow1 = Show::factory()->create(['initialized' => true]);
    $initializedShow2 = Show::factory()->create(['initialized' => true]);
    $notInitializedShow1 = Show::factory()->create(['initialized' => false]);
    $notInitializedShow2 = Show::factory()->create(['initialized' => false]);

    // Get initialized shows using the scope
    $initializedShows = Show::initialized()->get();

    // Assert we only get the initialized shows
    expect($initializedShows)->toHaveCount(2);
    expect($initializedShows->pluck('id')->toArray())
        ->toContain($initializedShow1->id)
        ->toContain($initializedShow2->id)
        ->not->toContain($notInitializedShow1->id)
        ->not->toContain($notInitializedShow2->id);
});

test('initialized scope can be chained with other queries', function () {
    // Create shows with various states
    Show::factory()->create([
        'initialized' => true,
        'name' => 'Breaking Bad',
        'external_id' => 1001,
    ]);

    Show::factory()->create([
        'initialized' => true,
        'name' => 'Better Call Saul',
        'external_id' => 1002,
    ]);

    Show::factory()->create([
        'initialized' => false,
        'name' => 'The Wire',
        'external_id' => 1003,
    ]);

    // Test chaining with pluck
    $initializedIds = Show::initialized()
        ->pluck('id', 'external_id')
        ->toArray();

    expect($initializedIds)->toHaveCount(2);
    expect($initializedIds)->toHaveKey(1001);
    expect($initializedIds)->toHaveKey(1002);
    expect($initializedIds)->not->toHaveKey(1003);

    // Test chaining with where
    $breakingBad = Show::initialized()
        ->where('name', 'Breaking Bad')
        ->first();

    expect($breakingBad)->not->toBeNull();
    expect($breakingBad->name)->toBe('Breaking Bad');

    // Test that non-initialized shows are not found
    $theWire = Show::initialized()
        ->where('name', 'The Wire')
        ->first();

    expect($theWire)->toBeNull();
});

test('initialized scope returns empty collection when no initialized shows exist', function () {
    // Create only non-initialized shows
    Show::factory()->count(3)->create(['initialized' => false]);

    $initializedShows = Show::initialized()->get();

    expect($initializedShows)->toBeEmpty();
});
