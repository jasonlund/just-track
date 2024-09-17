<?php

use App\Models\Show;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses()->group('Feature');

it("has a console command", function() {
    $this->artisan('tmdb:update-ids --dry-run')
        ->assertSuccessful();
});

it("will only run after 8 AM UTC today", function () {
    Carbon::setTestNow('2024-09-12 02:00:00');

    $this->artisan('tmdb:update-ids --dry-run')
        ->assertFailed();

    Carbon::setTestNow('2024-09-12 08:01:00');

    $this->artisan('tmdb:update-ids --dry-run')
        ->assertSuccessful();
});

it("it won't run in the future", function() {
    Carbon::setTestNow('2024-09-12 02:00:00');

    $this->artisan('tmdb:update-ids --date="09-13-2024" --dry-run')
        ->assertFailed();
});

it("it won't run for dates 3 months or more in the past", function() {
    Carbon::setTestNow('2024-09-12 02:00:00');

    $this->artisan('tmdb:update-ids --date="06-11-2024" --dry-run')
        ->assertFailed();
});

it("will process an entire file", function() {
    Carbon::setTestNow('2024-09-16 09:00:00');

    $this->artisan('tmdb:update-ids')
        ->expectsOutput('Created 1000 shows.');

    Storage::disk('local')->assertMissing('temp/tmdb/series-09_16_2024.json');

    expect(Show::count())
        ->toBe(1000);
});

it("will process a partial file if shows already exist", function() {
    Carbon::setTestNow('2024-09-16 09:00:00');

    $this->artisan('tmdb:update-ids');

    expect(Show::count())
        ->toBe(1000);

    $oldShow = Show::latest('external_id')->first();

    Carbon::setTestNow('2024-09-17 09:00:00');

    $this->artisan('tmdb:update-ids')
        ->expectsOutput('Created 132 shows.');

    expect($oldShow->wasChanged())
        ->toBeFalse()

        ->and(Show::count())
        ->toBe(1132);

});
