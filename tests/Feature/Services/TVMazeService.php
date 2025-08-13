<?php

use App\Services\TVMazeService;

uses()->group('tv_maze_service');

beforeEach(function () {
    $this->service = new TVMazeService;
});

it('returns search results', function () {
    expect($this->service->search('Doctor'))
        ->toHaveCount(10);
});

it('returns shows index', function () {
    expect($this->service->shows())
        ->toHaveCount(240);
});

it('shows index can be paginated', function () {

    // The pagination is based on show ID, e.g. page 0 will contain shows with IDs between 0 and 250.
    // This means a single page might contain less than 250 results, in case of deletions,
    // but it also guarantees that deletions won't cause shuffling in the page numbering for other shows.
    //
    // @see https://www.tvmaze.com/api#show-index
    expect($shows = $this->service->shows())
        ->toHaveCount(240)
        ->and(reset($shows))
        ->toMatchArray([
            'id' => 1,
        ])
        ->and(end($shows))
        ->toMatchArray([
            'id' => 249,
        ])

        ->and($shows = $this->service->shows(1))
        ->toHaveCount(245)
        ->and(reset($shows))
        ->toMatchArray([
            'id' => 250,
        ])
        ->and(end($shows))
        ->toMatchArray([
            'id' => 499,
        ])

        ->and($shows = $this->service->shows(2))
        ->toHaveCount(242)
        ->and(reset($shows))
        ->toMatchArray([
            'id' => 500,
        ])
        ->and(end($shows))
        ->toMatchArray([
            'id' => 749,
        ])

        ->and($shows = $this->service->shows(3))
        ->toBeFalse();
});
