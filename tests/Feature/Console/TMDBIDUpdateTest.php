<?php

use Carbon\Carbon;

it("has a console command", function() {
    $this->artisan('tmdb:update-ids')
        ->assertSuccessful();
});

it("will only run after 8 AM UTC today", function () {
    Carbon::setTestNow('2024-09-12 02:00:00');

    $this->artisan('tmdb:update-ids')
        ->assertFailed();

    Carbon::setTestNow('2024-09-12 08:01:00');

    $this->artisan('tmdb:update-ids')
        ->assertSuccessful();
});

it("it won't run in the future", function() {
    Carbon::setTestNow('2024-09-12 02:00:00');

    $this->artisan('tmdb:update-ids --date="09-13-2024"')
        ->assertFailed();
});

it("it won't run for dates 3 months or more in the past", function() {
    Carbon::setTestNow('2024-09-12 02:00:00');

    $this->artisan('tmdb:update-ids --date="06-11-2024"')
        ->assertFailed();
});

