<?php

use App\Livewire\Pages\Search;
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
