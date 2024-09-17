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

    // We need 14 seasons, including the specials.
    foreach(range(0, 13) as $number) {
        Season::factory([
            'show_id' => $show->id,
            'number' => $number
        ])->create();
    }

    expect($show->episodes()->count())
        ->toBe(0);

    Livewire::withoutLazyLoading()
        ->test(EpisodeList::class, ['show' => $show])
        ->assertSee("The Christmas Invasion");

    expect($show->episodes()->count())
        ->toBe(352);
});
