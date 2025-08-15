<?php

use App\Livewire\Pages\DashboardPreviouslyAired;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders successfully', function () {
    asUser();

    get(route('dashboard.previously-aired'))
        ->assertOk()
        ->assertSeeLivewire(DashboardPreviouslyAired::class);
});

it('lists previously aired episodes by air date', function () {
    // Arrange
    $user = asUser();

    Carbon::setTestNow('2024-09-01 00:00:00');

    $shows = Show::factory()
        ->has(
            Season::factory()
                ->has(Episode::factory(rand(10, 30), [
                    'air_timestamp' => fn () => Carbon::now()->subDays(rand(1, 365)),
                ]))
        )
        ->count(5)
        ->create();

    $episodes = Episode::where('air_timestamp', '<', Carbon::now()->subHours(2))
        ->orderByDesc('air_timestamp')
        ->get();

    $user->shows()->sync($shows);

    // Act & Assert
    Livewire::test(DashboardPreviouslyAired::class)
        ->assertSeeInOrder($episodes->pluck('name')->toArray());
});

it('groups past air dates together', function () {
    // Arrange
    $user = asUser();

    Carbon::setTestNow('2024-09-01 00:00:00');

    $shows = Show::factory()
        ->has(
            Season::factory()
                ->has(Episode::factory(rand(10, 20), [
                    'air_timestamp' => fn () => fake()->dateTimeBetween(Carbon::now()->subYear(), Carbon::now()->subDays(1)),
                ]))
        )
        ->count(5)
        ->create();

    $episodes = Episode::where('air_timestamp', '<', Carbon::now()->subHours(2))
        ->orderByDesc('air_timestamp')
        ->get()
        ->groupBy(function ($item) {
            $diff = $item->air_timestamp->diffInDays(Carbon::now());
            if ($diff < 7) {
                return 'd'.$item->air_timestamp->format('Y-m-d');
            } elseif ($diff < 28) {
                return 'w'.$item->air_timestamp->startOfWeek()->format('Y-m-d');
            } else {
                return 'm'.$item->air_timestamp->startOfMonth()->format('Y-m-d');
            }
        });

    $user->shows()->sync($shows);

    // Act & Assert
    Livewire::test(DashboardPreviouslyAired::class)
        ->assertSeeInOrder($episodes->map(function ($items, $key) {
            if (Str::startsWith($key, 'd')) {
                return $items->first()->air_timestamp->format('l');
            } elseif (Str::startsWith($key, 'w')) {
                return $items->first()->air_timestamp->startOfWeek()->format('M jS');
            } else {
                return $items->first()->air_timestamp->startOfMonth()->format('M Y');
            }
        })->toArray());
});

it('only shows episodes for subscribed shows', function () {
    // Arrange
    $user = asUser();

    Carbon::setTestNow('2024-09-01 00:00:00');

    $subscribedShow = Show::factory()
        ->has(
            Season::factory()
                ->has(Episode::factory(['name' => 'Subscribed Episode', 'air_timestamp' => Carbon::now()->subDays(1)]))
        )
        ->create();

    $unsubscribedShow = Show::factory()
        ->has(
            Season::factory()
                ->has(Episode::factory(['name' => 'Unsubscribed Episode', 'air_timestamp' => Carbon::now()->subDays(1)]))
        )
        ->create();

    $user->shows()->sync([$subscribedShow->id]);

    // Act & Assert
    Livewire::test(DashboardPreviouslyAired::class)
        ->assertSee('Subscribed Episode')
        ->assertDontSee('Unsubscribed Episode');
});
