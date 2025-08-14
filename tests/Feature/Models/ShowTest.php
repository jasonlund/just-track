<?php

use App\Models\Show;

test('initialized scope returns only initialized shows', function () {
    // Arrange
    $initializedShow1 = Show::factory()->create(['initialized' => true]);
    $initializedShow2 = Show::factory()->create(['initialized' => true]);
    $notInitializedShow1 = Show::factory()->create(['initialized' => false]);
    $notInitializedShow2 = Show::factory()->create(['initialized' => false]);

    // Act
    $initializedShows = Show::initialized()->get();

    // Assert
    expect($initializedShows)->toHaveCount(2)
        ->and($initializedShows->pluck('id')->toArray())
        ->toContain($initializedShow1->id)
        ->toContain($initializedShow2->id)
        ->not->toContain($notInitializedShow1->id)
        ->not->toContain($notInitializedShow2->id);
});

test('initialized scope can be chained with other queries', function () {
    // Arrange
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

    // Act
    $initializedIds = Show::initialized()
        ->pluck('id', 'external_id')
        ->toArray();

    $breakingBad = Show::initialized()
        ->where('name', 'Breaking Bad')
        ->first();

    $theWire = Show::initialized()
        ->where('name', 'The Wire')
        ->first();

    // Assert
    expect($initializedIds)->toHaveCount(2)
        ->toHaveKey(1001)
        ->toHaveKey(1002)
        ->not->toHaveKey(1003)
        ->and($breakingBad)->not->toBeNull()
        ->and($breakingBad->name)->toBe('Breaking Bad')
        ->and($theWire)->toBeNull();
});

test('initialized scope returns empty collection when no initialized shows exist', function () {
    // Arrange
    Show::factory()->count(3)->create(['initialized' => false]);

    // Act
    $initializedShows = Show::initialized()->get();

    // Assert
    expect($initializedShows)->toBeEmpty();
});
