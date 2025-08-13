<?php

use App\Models\Show;
use Carbon\Carbon;

uses()->group('Feature');

it('has a console command', function () {
    // Act & Assert
    $this->artisan('tv-maze:update-ids --dry-run')
        ->assertSuccessful();
});

it('processes results', function () {
    // Arrange
    Carbon::setTestNow('2025-03-01 00:00:00');

    // Act & Assert
    $this->artisan('tv-maze:update-ids')
        ->assertSuccessful();

    // Because of TVMaze's pagination, we process 3 pages of 250 "entries",
    // ending up with 727 total and the final id of 749.
    expect(Show::count())
        ->toBe(727)
        ->and(Show::latest('id')->first()->external_id)
        ->toBe(749);

    $doctorWho = Show::where('external_id', 210)
        ->first();

    expect($doctorWho->name)->toBe('Doctor Who')
        ->and($doctorWho->type)->toBe('scripted')
        ->and($doctorWho->language)->toBe('english')
        ->and($doctorWho->status)->toBe('ended')
        ->and($doctorWho->runtime)->toBeNull()
        ->and($doctorWho->average_runtime)->toBe(48)
        ->and($doctorWho->premiered->format('Y-m-d'))->toBe('2005-03-26')
        ->and($doctorWho->ended->format('Y-m-d'))->toBe('2022-10-23')
        ->and($doctorWho->tvdb_id)->toBe(78804)
        ->and($doctorWho->imdb_id)->toBe('tt0436992')
        ->and($doctorWho->image)->toBe('https://static.tvmaze.com/uploads/images/original_untouched/488/1220400.jpg')
        ->and($doctorWho->summary)->toBe('<p>Adventures across time and space with the time travelling alien and companions.</p>')
        ->and($doctorWho->external_updated_at->toISOString())->toBe('2025-01-22T23:42:35.000000Z')
        ->and($doctorWho->created_at->toISOString())->toBe('2025-03-01T00:00:00.000000Z')
        ->and($doctorWho->updated_at->toISOString())->toBe('2025-03-01T00:00:00.000000Z');
});

it('resumes from the last id', function () {
    Show::factory()->create([
        'external_id' => 700,
    ]);

    $this->artisan('tv-maze:update-ids')
        ->assertSuccessful();

    expect(Show::latest('id')->first()->external_id)
        ->toBe(749)
        ->and(Show::where('external_id', 699)->first())
        ->toBeNull();
});
