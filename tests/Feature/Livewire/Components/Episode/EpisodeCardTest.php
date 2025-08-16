<?php

use App\Livewire\Components\Episode\EpisodeCard;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    asUser();
});

it('renders episode information correctly', function () {
    // Arrange
    $show = Show::factory()->create(['name' => 'Breaking Bad']);
    $season = Season::factory()->create([
        'show_id' => $show->id,
        'number' => 3,
    ]);
    $episode = Episode::factory()->create([
        'season_id' => $season->id,
        'number' => 7,
        'name' => 'One Minute',
        'air_timestamp' => Carbon::parse('2010-05-02'),
    ]);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode])
        ->assertSee('Breaking Bad')
        ->assertSee('S03E07')
        ->assertSee('One Minute')
        ->assertSee('May 2, 2010');
});

it('handles episodes without air dates', function () {
    // Arrange
    $show = Show::factory()->create(['name' => 'Future Show']);
    $season = Season::factory()->create([
        'show_id' => $show->id,
        'number' => 1,
    ]);
    $episode = Episode::factory()->create([
        'season_id' => $season->id,
        'number' => 1,
        'name' => 'Pilot',
        'air_timestamp' => null,
    ]);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode])
        ->assertSee('Future Show')
        ->assertSee('S01E01')
        ->assertSee('Pilot')
        ->assertSee('TBA');
});

it('formats episode numbers with leading zeros', function () {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create([
        'show_id' => $show->id,
        'number' => 12,
    ]);
    $episode = Episode::factory()->create([
        'season_id' => $season->id,
        'number' => 24,
    ]);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode])
        ->assertSee('S12E24');
});

it('handles special season numbers correctly', function () {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create([
        'show_id' => $show->id,
        'number' => 0,
    ]);
    $episode = Episode::factory()->create([
        'season_id' => $season->id,
        'number' => 5,
    ]);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode])
        ->assertSee('S00E05');
});

it('displays runtime when available', function () {
    // Arrange
    $show = Show::factory()->create(['name' => 'Test Show']);
    $season = Season::factory()->create(['show_id' => $show->id]);
    $episode = Episode::factory()->create([
        'season_id' => $season->id,
        'runtime' => 42,
    ]);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode])
        ->assertSee('42 min');
});

it('does not display runtime when not available', function () {
    // Arrange
    $show = Show::factory()->create(['name' => 'Test Show']);
    $season = Season::factory()->create(['show_id' => $show->id]);
    $episode = Episode::factory()->create([
        'season_id' => $season->id,
        'runtime' => null,
    ]);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode])
        ->assertDontSee('min');
});

it('shows watch button when enabled and user has show attached', function () {
    // Arrange
    $user = auth()->user();
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);
    $episode = Episode::factory()->create(['season_id' => $season->id]);

    $user->shows()->attach($show);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode, 'showWatchButton' => true])
        ->assertSee('Mark as Watched');
});

it('does not show watch button when disabled', function () {
    // Arrange
    $user = auth()->user();
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);
    $episode = Episode::factory()->create(['season_id' => $season->id]);

    $user->shows()->attach($show);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode, 'showWatchButton' => false])
        ->assertDontSee('Mark as Watched');
});

it('does not show watch button when user does not have show attached', function () {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);
    $episode = Episode::factory()->create(['season_id' => $season->id]);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode, 'showWatchButton' => true])
        ->assertDontSee('Mark as Watched');
});

it('can mark episode as watched', function () {
    // Arrange
    $user = auth()->user();
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);
    $episode = Episode::factory()->create(['season_id' => $season->id]);

    $user->shows()->attach($show);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode, 'showWatchButton' => true])
        ->assertSee('Mark as Watched')
        ->call('toggleWatched')
        ->assertSee('Mark as Unwatched');

    expect($user->episodes->contains($episode->id))->toBeTrue();
});

it('can mark episode as unwatched', function () {
    // Arrange
    $user = auth()->user();
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);
    $episode = Episode::factory()->create(['season_id' => $season->id]);

    $user->shows()->attach($show);
    $user->episodes()->attach($episode);

    // Act & Assert
    Livewire::test(EpisodeCard::class, ['episode' => $episode, 'showWatchButton' => true])
        ->assertSee('Mark as Unwatched')
        ->call('toggleWatched')
        ->assertSee('Mark as Watched');

    expect($user->fresh()->episodes->contains($episode->id))->toBeFalse();
});
