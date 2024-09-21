<?php

use App\Livewire\Components\Show\EpisodeList;
use App\Livewire\Pages\ShowShow;
use App\Models\Season;
use App\Models\Show;
use Livewire\Livewire;
use function Pest\Laravel\get;

it("renders successfully", function () {
    asUser();

    $show = Show::factory()->create();

    get(route('show.show', [$show->external_id]))
        ->assertOk();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, [$show])
        ->assertSeeLivewire(EpisodeList::class);

    Livewire::withoutLazyLoading()
        ->test(EpisodeList::class, [$show])
        ->assertOk();
});

it("initializes a show's episodes if none exist", function() {
    asUser();

    $show = uninitDoctorWhoShowFactory()->create();

    expect($show->episodes()->count())
        ->toBe(0);

    // We test using ShowShow to initialize the show as well.
    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show])
        ->assertSee("The Christmas Invasion");

    expect($show->episodes()->count())
        ->toBe(352);
});

it("groups episodes by season", function() {
    asUser();

    $show = uninitDoctorWhoShowFactory()->create();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show])
        ->assertSeeInOrder([
            'Series 1',
            'Rose',
            'Series 2',
            'New Earth',
            'Flux',
            'The Vanquishers'
        ]);
});

it("lists season zero last", function() {
    asUser();

    $show = uninitDoctorWhoShowFactory()->create();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show])
        ->assertSeeInOrder([
            'Flux',
            'Specials',
        ]);
});
