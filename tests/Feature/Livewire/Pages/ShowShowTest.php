<?php

use App\Livewire\Pages\ShowShow;
use App\Models\Episode;
use App\Models\Show;
use Livewire\Livewire;
use function Pest\Laravel\get;

it("renders successfully", function () {
    asUser();

    $show = Show::factory()->create();

    get(route('show.show', [$show->external_id]))
        ->assertOk()
        ->assertSeeLivewire(ShowShow::class);
});

it("shows a show", function() {
    asUser();

    $show = Show::factory()
        ->has(Episode::factory()->count(100))
        ->create();
    $episodes = $show->episodes;
    $firstSeason = $episodes
        ->sortBy('season')
        ->unique('season')
        ->first()
        ->season;

    get(route('show.show', [$show->external_id]))
        ->assertSee($show->name)
        ->assertSee($show->year)
        ->assertSee($show->original_country)
        ->assertSee($show->overview)

        ->assertSeeInOrder($episodes
            ->sortBy('season')
            ->unique('season')
            ->map(function($item) {
                return 'Season ' . $item['season'];
            })
            ->toArray())

        ->assertSeeInOrder($episodes
            ->where('season', $firstSeason)
            ->sortBy('number')
            ->map(function($item) {
                return $item['number'];
            })
            ->toArray());
});
