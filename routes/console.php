<?php

use App\Console\Commands\TVMaze\TVMazeUpdateIDs;
use App\Console\Commands\TVMaze\UpdateInitializedShows;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(TVMazeUpdateIDs::class)
    ->daily()
    ->timezone('America/Los_Angeles')
    ->then(function () {
        Artisan::call(UpdateInitializedShows::class);
    });
