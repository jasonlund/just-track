<?php

use App\Console\Commands\FanArtTV\FanArtTVUpdateImages;
use App\Console\Commands\TVMaze\TVMazeUpdateIDs;
use App\Console\Commands\TVMaze\TVMazeUpdateInitializedShows;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(TVMazeUpdateIDs::class)
    ->daily()
    ->timezone('America/Los_Angeles')
    ->then(function () {
        Artisan::call(TVMazeUpdateInitializedShows::class);
        Artisan::call(FanArtTVUpdateImages::class);
    });
