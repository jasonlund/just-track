<?php

use App\Livewire\Pages\ShowShow;
use App\Models\Episode;
use App\Models\Show;
use Livewire\Livewire;
use function Pest\Laravel\get;

it("renders successfully", function () {
    asUser();

    $show = Show::factory()->create();

    get(route('show.show', [$show->external_id]))
        ->assertOk()
        ->assertSeeLivewire(ShowShow::class);
});

it("creates a show that does not already exists", function() {
    asUser();

    expect(Show::count())
        ->toBe(0);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['tvdb_id' => '78804'])

        ->assertSee('Doctor Who (2005)');

    $show = Show::first();

    expect(Show::count())
        ->toBe(1)

        ->and($show->name)->toBe('Doctor Who (2005)')
        ->and($show->external_id)->toBe(78804)

        ->and($show->episodes()->count())->toBe(322);
});

it("does not create a show that already exists", function() {
    asUser();

    expect(Show::count())
        ->toBe(0);

    Show::factory([
        'external_id' => 78804,
        'name' => 'Doctor Who (2005)',
    ])->create();

    expect(Show::count())
        ->toBe(1);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['tvdb_id' => '78804']);

    expect(Show::count())
        ->toBe(1);
});

it("only accepts an integer for the tvdb_id parameter", function () {
    asUser();

    get(route('show.show', ['tvdb_id' => 'foobar']))
        ->assertNotFound();

    Show::factory([
        'external_id' => 78804,
        'name' => 'Doctor Who (2005)',
    ])->create();

    get(route('show.show', ['tvdb_id' => '78804']))
        ->assertOk();
});

it("will handle a tvdb exception as not found", function() {
    asUser();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['tvdb_id' => '12345'])
        ->assertRedirect('/404');
});

it("will optionally attach the show to a user", function() {
    $user = asUser();

    Show::factory([
        'external_id' => 78804,
        'name' => 'Doctor Who (2005)',
    ])->create();

    expect($user->shows()->count())
        ->toBe(0);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['78804']);

    expect($user->shows()->count())
        ->toBe(0);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['78804', 'attach']);

    expect($user->shows()->count())
        ->toBe(1);
});

it("shows a show", function() {
    asUser();

    $show = Show::factory()
        ->has(Episode::factory()->count(100))
        ->create();
    $episodes = $show->episodes;
    $firstSeason = $episodes
        ->sortBy('season')
        ->unique('season')
        ->first()
        ->season;

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['tvdb_id' => $show->external_id])

        ->assertSee($show->name)
        ->assertSee($show->year)
        ->assertSee($show->original_country)
        ->assertSee($show->overview)

        ->assertSeeInOrder($episodes
            ->sortBy('season')
            ->unique('season')
            ->map(function($item) {
                return 'Season ' . $item['season'];
            })
            ->toArray())

        ->assertSeeInOrder($episodes
            ->where('season', $firstSeason)
            ->sortBy('number')
            ->map(function($item) {
                return $item['number'];
            })
            ->toArray());
});
