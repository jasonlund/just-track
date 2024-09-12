<?php

use App\Services\TMDBService;

uses()->group('tmdb_service');

it("returns results", function() {
    $service = new TMDBService();

    expect($service->get("search/tv", ['query' => 'Doc']))
        ->toHaveCount(20);
});

