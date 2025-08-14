<?php

use App\Livewire\Pages\Search;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders successfully', function () {
    // Arrange
    asUser();

    // Act & Assert
    get(route('search'))
        ->assertOk()
        ->assertSeeLivewire(Search::class);
});

it('is able to return search results', function () {
    // Arrange
    asUser();

    // Act & Assert
    Livewire::withQueryParams(['query' => 'Doctor'])
        ->test(Search::class)
        ->assertSee('Doctor Who')
        ->assertSeeHtml('<a href="'.route('show.show', ['210']).'" wire:navigate>')
        ->assertSeeHtml('<a href="'.route('show.show', ['210', 'attach']).'" wire:navigate>')
        ->assertViewHas('results', function ($results) {
            return count($results) == 10;
        });
});

it('should cache results', function () {
    // Arrange
    Carbon::setTestNow('2024-09-07 00:00:00');
    asUser();

    // Assert initial state
    expect(Cache::has('tv-maze-search-Doctor'))
        ->toBeFalse();

    // Act
    get(route('search', ['query' => 'Doctor']));

    // Assert cache was set
    expect(Cache::has('tv-maze-search-Doctor'))
        ->toBeTrue();

    // Act - advance time within cache period
    Carbon::setTestNow('2024-09-07 02:59:00');

    // Assert cache still exists
    expect(Cache::has('tv-maze-search-Doctor'))
        ->toBeTrue();

    // Act - advance time beyond cache period
    Carbon::setTestNow('2024-09-07 03:01:00');

    // Assert cache expired
    expect(Cache::has('tv-maze-search-Doctor'))
        ->toBeFalse();
});

it('can return handle no query string', function () {
    // Arrange
    asUser();

    // Act & Assert - with empty query
    Livewire::withQueryParams(['query' => ''])
        ->test(Search::class)
        ->assertSee('Please search for a show above')
        ->assertViewHas('results', function ($results) {
            return count($results) == 0;
        });

    // Act & Assert - without query param
    Livewire::test(Search::class)
        ->assertSee('Please search for a show above')
        ->assertViewHas('results', function ($results) {
            return count($results) == 0;
        });
});

it('can return no results', function () {
    // Arrange
    asUser();

    // Act & Assert
    Livewire::withQueryParams(['query' => 'foobar'])
        ->test(Search::class)
        ->assertSee('Your search returned no results')
        ->assertViewHas('results', function ($results) {
            return count($results) == 0;
        });
});

it('can reset the search query and results', function () {
    // Arrange
    asUser();

    // Act & Assert
    Livewire::withQueryParams(['query' => 'Doctor'])
        ->test(Search::class)
        ->assertViewHas('results', function ($results) {
            return count($results) == 10;
        })
        ->assertSeeHtml('<button wire:click="resetQuery">Reset Search</button>')
        ->call('resetQuery')
        ->assertSet('query', '')
        ->assertViewHas('results', function ($results) {
            return count($results) == 0;
        });
});
