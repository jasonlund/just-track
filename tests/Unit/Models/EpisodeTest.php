<?php

use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use App\Models\User;

beforeEach(function () {
    // Create a basic setup for most tests
    $this->show = Show::factory()->create();
    $this->season = Season::factory()->create(['show_id' => $this->show->id]);
    $this->episode = Episode::factory()->create(['season_id' => $this->season->id]);
    $this->user = User::factory()->create();
});

describe('relationships', function () {
    it('belongs to a season', function () {
        // Arrange & Act
        $relationship = $this->episode->season;

        // Assert
        expect($relationship)->toBeInstanceOf(Season::class)
            ->and($relationship->id)->toBe($this->season->id);
    });

    it('has a show through season using hasOneThrough', function () {
        // Arrange & Act
        $show = $this->episode->show;

        // Assert
        expect($show)->toBeInstanceOf(Show::class)
            ->and($show->id)->toBe($this->show->id);
    });

    it('belongs to many users', function () {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->episode->users()->attach([
            $user1->id => ['created_at' => now()],
            $user2->id => ['created_at' => now()->subDay()],
        ]);

        // Act
        $users = $this->episode->users;

        // Assert
        expect($users)->toHaveCount(2)
            ->and($users->pluck('id')->toArray())->toContain($user1->id, $user2->id);
    });

    it('has pivot data with created_at timestamp', function () {
        // Arrange
        $createdAt = now()->subDays(3);
        $this->episode->users()->attach($this->user->id, ['created_at' => $createdAt]);

        // Act
        $user = $this->episode->users()->first();

        // Assert
        expect($user->pivot->created_at)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($user->pivot->created_at->format('Y-m-d H:i:s'))->toBe($createdAt->format('Y-m-d H:i:s'));
    });
});

describe('scopes', function () {
    beforeEach(function () {
        // Create episodes with different watch states
        $this->watchedEpisode = Episode::factory()->create(['season_id' => $this->season->id]);
        $this->unwatchedEpisode = Episode::factory()->create(['season_id' => $this->season->id]);

        // Mark one as watched
        $this->user->episodes()->attach($this->watchedEpisode->id, ['created_at' => now()]);

        // Login as the user for auth-based scopes
        $this->actingAs($this->user);
    });

    it('filters watched episodes with scopeWatched', function () {
        // Act
        $watched = Episode::watched()->get();

        // Assert
        expect($watched)->toHaveCount(1)
            ->and($watched->first()->id)->toBe($this->watchedEpisode->id);
    });

    it('filters unwatched episodes with scopeUnwatched', function () {
        // Act
        $unwatched = Episode::unwatched()->get();

        // Assert - Should include the original episode from beforeEach and the unwatchedEpisode
        expect($unwatched->pluck('id')->toArray())
            ->toContain($this->episode->id, $this->unwatchedEpisode->id)
            ->not->toContain($this->watchedEpisode->id);
    });

    it('accepts custom userId parameter for scopeWatched', function () {
        // Arrange
        $otherUser = User::factory()->create();
        $otherEpisode = Episode::factory()->create(['season_id' => $this->season->id]);
        $otherUser->episodes()->attach($otherEpisode->id, ['created_at' => now()]);

        // Act
        $watchedByOther = Episode::watched($otherUser->id)->get();

        // Assert
        expect($watchedByOther)->toHaveCount(1)
            ->and($watchedByOther->first()->id)->toBe($otherEpisode->id);
    });

    it('accepts custom userId parameter for scopeUnwatched', function () {
        // Arrange
        $otherUser = User::factory()->create();

        // Act - Other user hasn't watched any episodes
        $unwatchedByOther = Episode::unwatched($otherUser->id)->get();

        // Assert - Should return all episodes for the other user
        expect($unwatchedByOther)->toHaveCount(3); // original + watched + unwatched episodes
    });
});

describe('attributes', function () {
    it('returns watched at timestamp when episode is watched', function () {
        // Arrange
        $watchedAt = now()->subDays(5);
        $this->actingAs($this->user);
        $this->user->episodes()->attach($this->episode->id, ['created_at' => $watchedAt]);

        // Act
        $timestamp = $this->episode->watchedAt;

        // Assert
        expect($timestamp)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($timestamp->format('Y-m-d H:i:s'))->toBe($watchedAt->format('Y-m-d H:i:s'));
    });

    it('returns null for watchedAt when episode is not watched', function () {
        // Arrange
        $this->actingAs($this->user);

        // Act
        $timestamp = $this->episode->watchedAt;

        // Assert
        expect($timestamp)->toBeNull();
    });

    it('returns null for watchedAt when no user is authenticated', function () {
        // Arrange - No user logged in

        // Act
        $timestamp = $this->episode->watchedAt;

        // Assert
        expect($timestamp)->toBeNull();
    });

    it('returns correct attached status', function () {
        // Arrange
        $this->actingAs($this->user);

        // Act & Assert - Not attached initially
        expect($this->episode->attached)->toBeFalse();

        // Attach the episode
        $this->user->episodes()->attach($this->episode->id);

        // Refresh the user's episodes collection
        $this->user->refresh();

        // Act & Assert - Should be attached now
        expect($this->episode->attached)->toBeTrue();
    });

    it('returns episode number or S for special episodes', function () {
        // Arrange
        $regularEpisode = Episode::factory()->create([
            'season_id' => $this->season->id,
            'number' => 5,
        ]);
        $specialEpisode = Episode::factory()->create([
            'season_id' => $this->season->id,
            'number' => null,
        ]);

        // Act & Assert
        expect($regularEpisode->number)->toBe(5)
            ->and($specialEpisode->number)->toBe('S');
    });

    it('returns season number through attribute', function () {
        // Arrange
        $season = Season::factory()->create(['show_id' => $this->show->id, 'number' => 3]);
        $episode = Episode::factory()->create(['season_id' => $season->id]);

        // Act & Assert
        expect($episode->seasonNumber)->toBe(3);
    });
});
