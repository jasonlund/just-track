<?php

use App\Livewire\Components\Show\EpisodeList;
use App\Livewire\Pages\ShowShow;
use App\Models\Show;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders successfully', function () {
    asUser();

    $show = doctorWhoShowFactory()->create();

    get(route('show.show', [$show->external_id]))
        ->assertOk();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, [$show])
        ->assertSeeLivewire(EpisodeList::class);

    Livewire::withoutLazyLoading()
        ->test(EpisodeList::class, [$show])
        ->assertOk();
});

it("initializes a show's episodes if none exist", function () {
    asUser();

    $show = doctorWhoShowFactory()->create();

    expect($show->episodes()->count())
        ->toBe(0);

    // We test using ShowShow to initialize the show as well.
    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show])
        ->assertSee('The Christmas Invasion');

    expect($show->episodes()->count())
        ->toBe(254);
});

it('groups episodes by season', function () {
    asUser();

    $show = doctorWhoShowFactory()->create();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show])
        ->assertSeeInOrder([
            'Season 1',
            'Rose',
            'The Christmas Invasion',
            'Season 2',
            'New Earth',
            'Flux',
        ]);
});

it('labels specials inside of their season', function () {
    asUser();

    $show = doctorWhoShowFactory()->create();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show])
        ->assertSeeInOrder([
            'Season 1',
            'S',
            'The Christmas Invasion',
            'Season 2',
        ]);
});

it('allows a user to attach an episode', function () {
    $user = asUser();

    $show = doctorWhoShowFactory()->create();

    $user->shows()->attach($show->id);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show]);

    Livewire::withoutLazyLoading()
        ->test(EpisodeList::class, ['show' => $show])
        ->assertSeeInOrder(['Mark as Watched', 'Mark as Watched', 'Mark as Watched', 'Mark as Watched', 'Mark as Watched'])
        ->call('sync', 3)
        ->assertSeeInOrder(['Mark as Watched', 'Mark as Watched', 'Mark as Unwatched', 'Mark as Watched', 'Mark as Watched']);

    expect($user->episodes)
        ->toHaveCount(1);
});

it('does not allow a user to mark an episode as watched unless the show belongs to the user', function () {
    asUser();

    $show = doctorWhoShowFactory()->create();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show]);

    Livewire::withoutLazyLoading()
        ->test(EpisodeList::class, ['show' => $show])
        ->assertDontSee('Mark as Watched')
        ->assertMethodNotWiredToForm('click')
        ->call('sync', 3)
        ->assertForbidden();
});

it('allows a user to detach an episode', function () {
    $user = asUser();

    $show = doctorWhoShowFactory()->create();

    $user->shows()->attach($show->id);
    $user->episodes()->attach(3);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show]);

    expect($user->episodes)
        ->toHaveCount(1);

    Livewire::withoutLazyLoading()
        ->test(EpisodeList::class, ['show' => $show])
        ->call('sync', 3);

    expect($user->fresh()->episodes)
        ->toHaveCount(0);
});
