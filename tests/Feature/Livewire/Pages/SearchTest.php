<?php

use App\Livewire\Pages\Search;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders successfully', function () {
    asUser();

    get(route('search'))
        ->assertOk()
        ->assertSeeLivewire(Search::class);
});

it('is able to return search results', function () {
    asUser();

    Livewire::withQueryParams(['query' => 'Doc'])
        ->test(Search::class)
        ->assertSee('Doctor Who')
        ->assertSeeHtml('<a href="'.route('show.show', ['57243']).'" wire:navigate>')
        ->assertSeeHtml('<a href="'.route('show.show', ['57243', 'attach']).'" wire:navigate>')
        ->assertViewHas('results', function ($results) {
            return count($results) == 20;
        });
});

it('should cache results', function () {
    // Arrange
    Carbon::setTestNow('2024-09-07 00:00:00');

    asUser();

    // Act & Assert
    expect(Cache::has('tmdb-search-Doc'))
        ->toBeFalse();

    get(route('search', ['query' => 'Doc']));

    expect(Cache::has('tmdb-search-Doc'))
        ->toBeTrue();

    Carbon::setTestNow('2024-09-07 02:59:00');

    expect(Cache::has('tmdb-search-Doc'))
        ->toBeTrue();

    Carbon::setTestNow('2024-09-07 03:01:00');

    expect(Cache::has('tmdb-search-Doc'))
        ->toBeFalse();
});

it('can return handle no query string', function () {
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

it('can return no results', function () {
    asUser();

    Livewire::withQueryParams(['query' => 'foobar'])
        ->test(Search::class)
        ->assertSee('Your search returned no results')
        ->assertViewHas('results', function ($results) {
            return count($results) == 0;
        });
});

it('can reset the search query and results', function () {
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
