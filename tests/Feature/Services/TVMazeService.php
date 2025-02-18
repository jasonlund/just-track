<?php

use App\Services\TVMazeService;

uses()->group('tv_maze_service');

it('returns results', function () {
    $service = new TVMazeService();

    expect($service->get('search/shows', ['q' => 'Doctor']))
        ->toHaveCount(10);
});
