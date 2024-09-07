<?php

use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Search;
use App\Models\Show;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use function Pest\Laravel\get;

it("renders successfully", function () {
    asUser();

    get(route('search'))
        ->assertOk()
        ->assertSeeLivewire(Search::class);
});

it("is able to return search results", function() {
    asUser();

    Livewire::withQueryParams(['query' => 'Doc'])
        ->test(Search::class)
        ->assertSee('Doctor Who (2005)')
        ->assertViewHas('results', function ($results) {
            return count($results) == 20;
        });
});

it("should cache results", function() {
    // Arrange
    Carbon::setTestNow('2024-09-07 00:00:00');

    asUser();

    // Act & Assert
    expect(Cache::has('tvdb-search-Doc'))
        ->toBeFalse();

    get(route('search', ['query' => 'Doc']));

    expect(Cache::has('tvdb-search-Doc'))
        ->toBeTrue();

    Carbon::setTestNow('2024-09-07 02:59:00');

    expect(Cache::has('tvdb-search-Doc'))
        ->toBeTrue();

    Carbon::setTestNow('2024-09-07 03:01:00');

    expect(Cache::has('tvdb-search-Doc'))
        ->toBeFalse();
});

it("can return handle no query string", function () {
    asUser();

    Livewire::withQueryParams(['query' => ''])
        ->test(Search::class)
        ->assertSee('Please search for a show above')
        ->assertViewHas('results', function ($results) {
            return count($results) == 0;
        });

    Livewire::test(Search::class)
        ->assertSee('Please search for a show above')
        ->assertViewHas('results', function ($results) {
            return count($results) == 0;
        });
});

it("can return no results", function() {
    asUser();

    Livewire::withQueryParams(['query' => 'foobar'])
        ->test(Search::class)
        ->assertSee('Your search returned no results')
        ->assertViewHas('results', function ($results) {
            return count($results) == 0;
        });
});


it("can reset the search query and results", function() {
    asUser();

    Livewire::withQueryParams(['query' => 'Doc'])
        ->test(Search::class)
        ->assertViewHas('results', function ($results) {
            return count($results) == 20;
        })
        ->assertSeeHtml('<button wire:click="resetQuery">Reset Search</button>')
        ->call('resetQuery')
        ->assertSet('query', '')
        ->assertViewHas('results', function ($results) {
            return count($results) == 0;
        });
});

it("can add a show that was searched for", function() {
    asUser();

    expect(Show::count())
        ->toBe(0);

    Livewire::withQueryParams(['query' => 'Doc'])
        ->test(Search::class)

        ->assertMethodWiredToAction('click', 'addShow(78804)')

        ->call('addShow', '78804')

        // TODO -- assertSee whenever we list shows on the dashboard.
        ->assertRedirectToRoute('dashboard')
        ->assertRedirect(Dashboard::class);

    $show = Show::first();

    expect(Show::count())
        ->toBe(1)

        ->and($show->name)->toBe('Doctor Who (2005)')
        ->and($show->external_id)->toBe(78804)

        ->and($show->episodes()->count())->toBe(322);
});

it("won't add a show that already exists", function () {
    asUser();

    expect(Show::count())
        ->toBe(0);

    Show::factory([
        'external_id' => 78804,
        'name' => 'Doctor Who (2005)',
    ])->create();

    expect(Show::count())
        ->toBe(1);

    Livewire::test(Search::class)
        ->call('addShow', '78804');

    expect(Show::count())
        ->toBe(1);
});

it("will associate the added show to the authenticated user", function() {
    $user = asUser();

    expect($user->shows()->count())
        ->toBe(0);

    // A newly created show.
    Livewire::test(Search::class)
        ->call('addShow', '78804');

    expect($user->shows()->count())
        ->toBe(1);

    // An existing show.
    Show::factory([
        'external_id' => '78805',           // For the very rare case that we randomly generate 78804
    ])->create();

    Livewire::test(Search::class)
        ->call('addShow', '78805');

    expect($user->shows()->count())
        ->toBe(2);
});
