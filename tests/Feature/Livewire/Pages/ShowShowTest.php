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

it("initializes a show that is not already", function() {
    asUser();

    $show = uninitDoctorWhoShowFactory()->create();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show])

        ->assertSee('Doctor Who');

    expect($show->wasChanged())
        ->toBeTrue()

        ->and($show->name)->toBe('Doctor Who')
        ->and($show->external_id)->toBe(57243)

        ->and($show->seasons()->count())->toBe(14)
        ->and($show->seasons()->first()->name)->toBe("Specials");

});

it("does not initialize a show that is already", function() {
    asUser();

    $show = Show::factory([
        'external_id' => 57243,
        'name' => 'Doctor Who',
        'original_name' => 'Doctor Who',
    ])->create();

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, ['show' => $show]);

    expect($show->wasChanged())
        ->toBeFalse();
});

it("only shows existing shows", function () {
    asUser();

    get(route('show.show', ['show' => 'foobar']))
        ->assertNotFound();

    get(route('show.show', ['show' => 57243]))
        ->assertNotFound();

    Show::factory([
        'external_id' => 57243,
        'original_name' => 'Doctor Who (2005)',
    ])->create();

    get(route('show.show', ['show' => 57243]))
        ->assertOk();
});

it("will optionally attach the show to a user", function() {
    $user = asUser();

    $show = Show::factory()->create();

    expect($user->shows()->count())
        ->toBe(0);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, [$show]);

    expect($user->shows()->count())
        ->toBe(0);

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, [$show, 'attach']);

    expect($user->shows()->count())
        ->toBe(1);
});

it("shows a show", function() {
    asUser();

    $show = Show::factory()
//        ->has(Episode::factory()->count(100))
        ->create();
//    $episodes = $show->episodes;
//    $firstSeason = $episodes
//        ->sortBy('season')
//        ->unique('season')
//        ->first()
//        ->season;

    Livewire::withoutLazyLoading()
        ->test(ShowShow::class, [$show])

        ->assertSee($show->name)
        ->assertSee($show->first_air_date)
        ->assertSee($show->origin_country)
        ->assertSee($show->overview)

//        ->assertSeeInOrder($episodes
//            ->sortBy('season')
//            ->unique('season')
//            ->map(function($item) {
//                return 'Season ' . $item['season'];
//            })
//            ->toArray())
//
//        ->assertSeeInOrder($episodes
//            ->where('season', $firstSeason)
//            ->sortBy('number')
//            ->map(function($item) {
//                return $item['number'];
//            })
//            ->toArray())
    ;
});
