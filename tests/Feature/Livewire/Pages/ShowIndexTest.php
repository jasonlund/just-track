<?php

use App\Livewire\Pages\ShowIndex as Page;
use App\Models\Show;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders successfully', function () {
    asUser();

    get(route('show.index'))
        ->assertOk()
        ->assertSeeLivewire(Page::class);
});

it('shows the index of shows', function () {
    // Arrange

    $shows = Show::factory()
        ->count(3)
        ->create();

    $user = asUser();

    $user->shows()->attach($shows);

    // Act & Assert

    Livewire::test(Page::class)
        ->assertSee($shows[0]['name'])
        ->assertSee($shows[0]['year'])

        ->assertSee($shows[1]['name'])
        ->assertSee($shows[1]['year'])

        ->assertSee($shows[2]['name'])
        ->assertSee($shows[2]['year']);
});

it('only shows shows the user is subscribed to', function () {
    // Arrange

    $shows = Show::factory()
        ->count(3)
        ->create();

    $user = asUser();

    $user->shows()->attach($shows->take(2));

    // Act & Assert

    Livewire::test(Page::class)
        ->assertSee($shows[0]['name'])
        ->assertSee($shows[1]['name'])
        ->assertDontSee($shows[2]['name']);
});

it('links to each show', function () {
    // Arrange

    $shows = Show::factory()
        ->count(3)
        ->create();

    $user = asUser();

    $user->shows()->attach($shows);

    // Act & Assert

    Livewire::test(Page::class)
        ->assertSeeHtml('<a href="'.route('show.show', $shows->first()).'" wire:navigate>');
});

it('orders shows')
    ->todo();
