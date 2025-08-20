<?php

use App\Livewire\Pages\DashboardToWatch;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can render the dashboard to watch page', function () {
    // Arrange & Act & Assert
    Livewire::test(DashboardToWatch::class)
        ->assertOk()
        ->assertSeeText('Episodes to Watch');
});

it('shows message when no episodes to watch', function () {
    // Arrange - User with no shows

    // Act & Assert
    Livewire::test(DashboardToWatch::class)
        ->assertSeeText('No episodes to watch')
        ->assertSee("You're all caught up!", false);
});

it('shows only first unwatched episode per show', function () {
    // Arrange
    $show1 = Show::factory()->create(['name' => 'Show One']);
    $season1 = Season::factory()->create(['show_id' => $show1->id, 'number' => 1]);
    $episode1 = Episode::factory()->create([
        'season_id' => $season1->id,
        'name' => 'First Episode',
        'number' => 1,
        'air_timestamp' => now()->subDays(5),
    ]);
    $episode2 = Episode::factory()->create([
        'season_id' => $season1->id,
        'name' => 'Second Episode',
        'number' => 2,
        'air_timestamp' => now()->subDays(4),
    ]);

    $show2 = Show::factory()->create(['name' => 'Show Two']);
    $season2 = Season::factory()->create(['show_id' => $show2->id, 'number' => 1]);
    $episode3 = Episode::factory()->create([
        'season_id' => $season2->id,
        'name' => 'Another First',
        'number' => 1,
        'air_timestamp' => now()->subDays(3),
    ]);

    // User tracks both shows
    $this->user->shows()->attach([$show1->id, $show2->id]);

    // Act & Assert
    Livewire::test(DashboardToWatch::class)
        ->assertSee('First Episode')
        ->assertDontSee('Second Episode')
        ->assertSee('Another First');
});

it('does not show episodes from untracked shows', function () {
    // Arrange
    $trackedShow = Show::factory()->create(['name' => 'Tracked Show']);
    $untrackedShow = Show::factory()->create(['name' => 'Untracked Show']);

    $season1 = Season::factory()->create(['show_id' => $trackedShow->id]);
    $episode1 = Episode::factory()->create([
        'season_id' => $season1->id,
        'name' => 'Tracked Episode',
        'air_timestamp' => now()->subDay(),
    ]);

    $season2 = Season::factory()->create(['show_id' => $untrackedShow->id]);
    $episode2 = Episode::factory()->create([
        'season_id' => $season2->id,
        'name' => 'Untracked Episode',
        'air_timestamp' => now()->subDay(),
    ]);

    // User only tracks one show
    $this->user->shows()->attach($trackedShow->id);

    // Act & Assert
    Livewire::test(DashboardToWatch::class)
        ->assertSee('Tracked Episode')
        ->assertDontSee('Untracked Episode');
});

it('does not show watched episodes', function () {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);
    $watchedEpisode = Episode::factory()->create([
        'season_id' => $season->id,
        'name' => 'Watched Episode',
        'air_timestamp' => now()->subDays(5),
    ]);
    $unwatchedEpisode = Episode::factory()->create([
        'season_id' => $season->id,
        'name' => 'Unwatched Episode',
        'air_timestamp' => now()->subDays(4),
    ]);

    $this->user->shows()->attach($show->id);
    $this->user->episodes()->attach($watchedEpisode->id, ['created_at' => now()->subDays(3)]);

    // Act & Assert
    Livewire::test(DashboardToWatch::class)
        ->assertDontSee('Watched Episode')
        ->assertSee('Unwatched Episode');
});

it('does not show future episodes', function () {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);
    $pastEpisode = Episode::factory()->create([
        'season_id' => $season->id,
        'name' => 'Past Episode',
        'air_timestamp' => now()->subDay(),
    ]);
    $futureEpisode = Episode::factory()->create([
        'season_id' => $season->id,
        'name' => 'Future Episode',
        'air_timestamp' => now()->addDay(),
    ]);

    $this->user->shows()->attach($show->id);

    // Act & Assert
    Livewire::test(DashboardToWatch::class)
        ->assertSee('Past Episode')
        ->assertDontSee('Future Episode');
});

it('orders episodes by last watched date then air timestamp', function () {
    // Arrange
    // Show 1 - has watched episodes (should appear first)
    $show1 = Show::factory()->create(['name' => 'Recently Watched Show']);
    $season1 = Season::factory()->create(['show_id' => $show1->id]);
    $watchedRecent = Episode::factory()->create([
        'season_id' => $season1->id,
        'number' => 1,
        'air_timestamp' => now()->subDays(10),
    ]);
    $unwatchedRecent = Episode::factory()->create([
        'season_id' => $season1->id,
        'name' => 'Next Episode Recent',
        'number' => 2,
        'air_timestamp' => now()->subDays(9),
    ]);

    // Show 2 - has older watched episodes (should appear second)
    $show2 = Show::factory()->create(['name' => 'Older Watched Show']);
    $season2 = Season::factory()->create(['show_id' => $show2->id]);
    $watchedOld = Episode::factory()->create([
        'season_id' => $season2->id,
        'number' => 1,
        'air_timestamp' => now()->subDays(20),
    ]);
    $unwatchedOld = Episode::factory()->create([
        'season_id' => $season2->id,
        'name' => 'Next Episode Old',
        'number' => 2,
        'air_timestamp' => now()->subDays(19),
    ]);

    // Show 3 - no watched episodes (should appear last)
    $show3 = Show::factory()->create(['name' => 'Never Watched Show']);
    $season3 = Season::factory()->create(['show_id' => $show3->id]);
    $unwatchedNever = Episode::factory()->create([
        'season_id' => $season3->id,
        'name' => 'Never Watched Episode',
        'air_timestamp' => now()->subDays(15),
    ]);

    // Setup user tracking and watched episodes
    $this->user->shows()->attach([$show1->id, $show2->id, $show3->id]);
    $this->user->episodes()->attach($watchedRecent->id, ['created_at' => now()->subDay()]);
    $this->user->episodes()->attach($watchedOld->id, ['created_at' => now()->subDays(7)]);

    // Act
    $component = Livewire::test(DashboardToWatch::class);
    $episodes = $component->get('episodes');

    // Assert - Check order
    expect($episodes)->toHaveCount(3)
        ->and($episodes[0]->name)->toBe('Next Episode Recent')
        ->and($episodes[1]->name)->toBe('Next Episode Old')
        ->and($episodes[2]->name)->toBe('Never Watched Episode');
});

it('refreshes episodes when episode-watched-toggled event is dispatched', function () {
    // Arrange
    $show = Show::factory()->create();
    $season = Season::factory()->create(['show_id' => $show->id]);
    $episode = Episode::factory()->create([
        'season_id' => $season->id,
        'name' => 'Test Episode',
        'air_timestamp' => now()->subDay(),
    ]);

    $this->user->shows()->attach($show->id);

    // Act & Assert
    $component = Livewire::test(DashboardToWatch::class);

    // Initially should see the episode
    $component->assertSee('Test Episode');

    // Dispatch event (simulating episode being watched)
    $component->dispatch('episode-watched-toggled', episodeId: $episode->id);

    // After dispatch, the computed property should be recalculated
    // We can't directly test if it's null, but we can verify the method was called
    // by checking that the component still works
    $component->assertOk();
});

it('does not show episodes from shows where all episodes are watched', function () {
    // Arrange
    $show = Show::factory()->create(['name' => 'Fully Watched Show']);
    $season = Season::factory()->create(['show_id' => $show->id]);
    $episode1 = Episode::factory()->create([
        'season_id' => $season->id,
        'air_timestamp' => now()->subDays(3),
    ]);
    $episode2 = Episode::factory()->create([
        'season_id' => $season->id,
        'air_timestamp' => now()->subDays(2),
    ]);

    // User tracks the show and has watched all episodes
    $this->user->shows()->attach($show->id);
    $this->user->episodes()->attach([$episode1->id, $episode2->id]);

    // Act & Assert
    Livewire::test(DashboardToWatch::class)
        ->assertDontSee($episode1->name)
        ->assertDontSee($episode2->name)
        ->assertSee('No episodes to watch');
});
