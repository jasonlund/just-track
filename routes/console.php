<?php

use App\Console\Commands\TVMaze\TVMazeUpdateIDs;
use Illuminate\Support\Facades\Schedule;

Schedule::command(TVMazeUpdateIDs::class)
    ->daily();
