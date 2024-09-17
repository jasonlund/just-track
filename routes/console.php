<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tmdb:update-ids')
    ->dailyAt('08:00');
