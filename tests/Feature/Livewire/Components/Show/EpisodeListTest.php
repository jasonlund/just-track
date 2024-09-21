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

it("allows a user to attach an episode", function() {
    $user = asUser();

    $show = uninitDoctorWhoShowFactory()->create();

    $user->shows()->attach($show->id);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show]);

    Livewire::withoutLazyLoading()
        ->test(EpisodeList::class, ['show' => $show])
//        ->assertMethodWiredToForm('sync')         // This doesn't work because I'm passing the episode id with the
        // action like sync(1234).
        // Maybe that's wrong?
        ->assertSeeInOrder(['Mark as Watched', 'Mark as Watched', 'Mark as Watched', 'Mark as Watched', 'Mark as Watched'])
        ->call('sync', 204)
        ->assertSeeInOrder(['Mark as Watched', 'Mark as Watched', 'Mark as Watched', 'Mark as Unwatched', 'Mark as Watched']);

    expect($user->episodes)
        ->toHaveCount(1);
});

it("does not allow a user to mark an episode as watched unless the show belongs to the user", function() {
    asUser();

    $show = uninitDoctorWhoShowFactory()->create();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show]);

    Livewire::withoutLazyLoading()
        ->test(EpisodeList::class, ['show' => $show])
        ->assertDontSee('Mark as Watched')
        ->assertMethodNotWiredToForm('click')
        ->call('sync', 204)
        ->assertForbidden();
});

it("allows a user to detach an episode", function() {
    $user = asUser();

    $show = uninitDoctorWhoShowFactory()->create();

    $user->shows()->attach($show->id);
    $user->episodes()->attach(204);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show]);

    expect($user->episodes)
        ->toHaveCount(1);

    Livewire::withoutLazyLoading()
        ->test(EpisodeList::class, ['show' => $show])
//        ->assertMethodWiredToForm('sync')         // This doesn't work because I'm passing the episode id with the
                                                    // action like sync(1234).
                                                    // Maybe that's wrong?
        ->call('sync', 204);

    expect($user->fresh()->episodes)
        ->toHaveCount(0);
});
