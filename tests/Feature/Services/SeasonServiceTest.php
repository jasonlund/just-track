<?php

use App\Models\Season;
use App\Models\Show;
use App\Services\SeasonService;

beforeEach(function () {
    $this->service = new SeasonService;
    $this->show = Show::factory()->create();
});

test('creates season from api data', function () {
    $seasonData = [
        'id' => 12345,
        'number' => 1,
        'name' => 'Season 1',
        'image' => [
            'original' => 'https://example.com/image.jpg',
        ],
    ];

    $season = $this->service->create($this->show, $seasonData);

    expect($season)->toBeInstanceOf(Season::class);
    expect($season->show_id)->toBe($this->show->id);
    expect($season->external_id)->toBe(12345);
    expect($season->number)->toBe(1);
    expect($season->name)->toBe('Season 1');
    expect($season->image)->toBe('https://example.com/image.jpg');

    $this->assertDatabaseHas('seasons', [
        'show_id' => $this->show->id,
        'external_id' => 12345,
        'number' => 1,
        'name' => 'Season 1',
        'image' => 'https://example.com/image.jpg',
    ]);
});

test('creates season without image', function () {
    $seasonData = [
        'id' => 67890,
        'number' => 2,
        'name' => 'Season 2',
    ];

    $season = $this->service->create($this->show, $seasonData);

    expect($season)->toBeInstanceOf(Season::class);
    expect($season->show_id)->toBe($this->show->id);
    expect($season->external_id)->toBe(67890);
    expect($season->number)->toBe(2);
    expect($season->name)->toBe('Season 2');
    expect($season->image)->toBeNull();

    $this->assertDatabaseHas('seasons', [
        'show_id' => $this->show->id,
        'external_id' => 67890,
        'number' => 2,
        'name' => 'Season 2',
        'image' => null,
    ]);
});

test('creates season with null image when image array exists but no original key', function () {
    $seasonData = [
        'id' => 11111,
        'number' => 3,
        'name' => 'Season 3',
        'image' => [
            'medium' => 'https://example.com/medium.jpg',
        ],
    ];

    $season = $this->service->create($this->show, $seasonData);

    expect($season)->toBeInstanceOf(Season::class);
    expect($season->image)->toBeNull();

    $this->assertDatabaseHas('seasons', [
        'show_id' => $this->show->id,
        'external_id' => 11111,
        'image' => null,
    ]);
});

test('creates season with show id instead of show instance', function () {
    $seasonData = [
        'id' => 99999,
        'number' => 4,
        'name' => 'Season 4',
        'image' => [
            'original' => 'https://example.com/season4.jpg',
        ],
    ];

    $season = $this->service->create($this->show->id, $seasonData);

    expect($season)->toBeInstanceOf(Season::class);
    expect($season->show_id)->toBe($this->show->id);
    expect($season->external_id)->toBe(99999);
    expect($season->number)->toBe(4);
    expect($season->name)->toBe('Season 4');
    expect($season->image)->toBe('https://example.com/season4.jpg');

    $this->assertDatabaseHas('seasons', [
        'show_id' => $this->show->id,
        'external_id' => 99999,
        'number' => 4,
        'name' => 'Season 4',
    ]);
});
