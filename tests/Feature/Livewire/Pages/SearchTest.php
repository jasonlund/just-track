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
        ->assertSee(route('show.show', ['210']))
        ->assertSee(route('show.show', ['210', 'attach']))
        ->assertViewHas('results', function ($results) {
            return count($results) == 10;
        });
});

it('should cache results', function () {
    // Arrange
    Carbon::setTestNow('2024-09-07 00:00:00');
    asUser();

    // Act
    $cacheExistsBefore = Cache::has('tv-maze-search-Doctor');
    get(route('search', ['query' => 'Doctor']));
    $cacheExistsAfterSearch = Cache::has('tv-maze-search-Doctor');

    Carbon::setTestNow('2024-09-07 02:59:00');
    $cacheExistsBeforeExpiry = Cache::has('tv-maze-search-Doctor');

    Carbon::setTestNow('2024-09-07 03:01:00');
    $cacheExistsAfterExpiry = Cache::has('tv-maze-search-Doctor');

    // Assert
    expect($cacheExistsBefore)->toBeFalse()
        ->and($cacheExistsAfterSearch)->toBeTrue()
        ->and($cacheExistsBeforeExpiry)->toBeTrue()
        ->and($cacheExistsAfterExpiry)->toBeFalse();
});

it('can return handle no query string', function () {
    // Arrange
    asUser();

    // Act & Assert
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
        ->assertSee('Reset Search')
        ->assertMethodWired('resetQuery')
        ->call('resetQuery')
        ->assertSet('query', '')
        ->assertViewHas('results', function ($results) {
            return count($results) == 0;
        });
});
