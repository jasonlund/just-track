<?php

use App\Models\Show;

uses()->group('Feature');

it('has a console command', function () {
    $this->artisan('tv-maze:update-ids --dry-run')
        ->assertSuccessful();
});

it('processes results', function () {
    $this->artisan('tv-maze:update-ids')
        ->assertSuccessful();

    // Because of TVMaze's pagination, we process 3 pages of 250 "entries",
    // ending up with 727 total and the final id of 749.
    expect(Show::count())
        ->toBe(727)
        ->and(Show::latest('id')->first()->external_id)
        ->toBe(749);
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
