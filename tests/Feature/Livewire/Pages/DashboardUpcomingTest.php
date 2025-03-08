<?php

use App\Livewire\Pages\DashboardUpcoming;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

it("renders successfully", function () {
    asUser();

    get(route('dashboard.upcoming'))
        ->assertOk()
        ->assertSeeLivewire(DashboardUpcoming::class);
});

it("lists upcoming episodes by air date", function () {
    // Arrange
    $user = asUser();

    Carbon::setTestNow('2024-09-01 00:00:00');

    $shows = Show::factory()
        ->has(
            Season::factory()
                ->has(Episode::factory(rand(10, 30), [
                    'air_timestamp' => fn () => Carbon::now()->addDays(rand(0, 365))
                ]))
        )
        ->count(5)
        ->create();

    $episodes = Episode::all()->sortBy('air_timestamp');

    $user->shows()->sync($shows);

    // Act & Assert
    Livewire::test(DashboardUpcoming::class)
        ->assertSeeInOrder($episodes->pluck('name')->toArray());
});

it("groups air dates together", function () {
    // Arrange
    $user = asUser();

    Carbon::setTestNow('2024-09-01 00:00:00');

    $shows = Show::factory()
        ->has(
            Season::factory()
                ->has(Episode::factory(rand(10,20), [
                    // Couldn't for the life of me figure out how to use faker here.
                    // It didn't respect my Carbon setTestNow, so Carbon will do.
                    'air_timestamp' => fn () => Carbon::now()->addDays(rand(0, 365))
                ]))
        )
        ->count(5)
        ->create();

    $episodes = Episode::all()
        ->sortBy('air_timestamp')
        ->groupBy(function($item) {
            $diff = Carbon::now()->diffInDays($item->air_timestamp);
            if($diff < 7) {
                return 'd' . $item->air_timestamp->format('Y-m-d');
            }else if($diff < 28) {
                return 'w'. $item->air_timestamp->startOfWeek()->format('Y-m-d');
            } else {
                return 'm'. $item->air_timestamp->startOfMonth()->format('Y-m-d');
            }
        });

    $user->shows()->sync($shows);

    // Act & Assert
    Livewire::test(DashboardUpcoming::class)
        ->assertSeeInOrder($episodes->map(function($items, $key) {
            if(Str::startsWith($key, 'd')) {
                return $items->first()->air_timestamp->format('l');
            }else if(Str::startsWith($key, 'w')) {
                return $items->first()->air_timestamp->startOfWeek()->format('M jS');
            }else{
                return $items->first()->air_timestamp->startOfMonth()->format('M');
            }
        })->toArray());
});
