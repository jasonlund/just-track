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

    expect($doctorWho)
        ->toMatchArray([
            'name' =>  'Doctor Who',
            'type' => 'scripted',
            'language' => 'english',
            'status' => 'ended',
            'runtime' => null,
            'average_runtime' => 48,
            'premiered' => '2005-03-26',
            'ended' => '2022-10-23',
            'tvdb_id' => 78804,
            'imdb_id' => 'tt0436992',
            'image' => 'https://static.tvmaze.com/uploads/images/original_untouched/488/1220400.jpg',
            'summary' => '<p>Adventures across time and space with the time travelling alien and companions.</p>',
            'external_updated_at' => '2025-01-22T23:42:35.000000Z',
            'created_at' => '2025-03-01T00:00:00.000000Z',
            'updated_at' => '2025-03-01T00:00:00.000000Z'
        ]);
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
