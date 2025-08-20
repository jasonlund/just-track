<?php

namespace App\Livewire\Pages;

use App\Models\Episode;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class DashboardToWatch extends Component
{
    #[Computed]
    public function episodes()
    {
        return Episode::query()
            ->join('seasons', 'seasons.id', '=', 'episodes.season_id')
            ->whereHas('show', function (Builder $query) {
                $query->whereIn('shows.id', auth()->user()->shows->pluck('id'));
            })
            ->whereNotIn('episodes.id', auth()->user()->episodes->pluck('id'))
            ->where('air_timestamp', '<=', now())
            ->whereIn('episodes.id', function ($query) {
                // Subquery to get the first unwatched episode ID per show
                $query->selectRaw('MIN(e.id)')
                    ->from('episodes as e')
                    ->join('seasons as s', 's.id', '=', 'e.season_id')
                    ->join('shows as sh', 'sh.id', '=', 's.show_id')
                    ->join('show_user as su', function ($join) {
                        $join->on('su.show_id', '=', 'sh.id')
                            ->where('su.user_id', '=', auth()->id());
                    })
                    ->leftJoin('episode_user as eu', function ($join) {
                        $join->on('eu.episode_id', '=', 'e.id')
                            ->where('eu.user_id', '=', auth()->id());
                    })
                    ->whereNull('eu.id') // Unwatched episodes
                    ->where('e.air_timestamp', '<=', now())
                    ->groupBy('sh.id');
            })
            ->select('episodes.*')
            ->addSelect(['last_watched_at' => function ($query) {
                $query->selectRaw('MAX(episode_user.created_at)')
                    ->from('episode_user')
                    ->join('episodes as e2', 'e2.id', '=', 'episode_user.episode_id')
                    ->join('seasons as s2', 's2.id', '=', 'e2.season_id')
                    ->whereColumn('s2.show_id', 'seasons.show_id')
                    ->where('episode_user.user_id', auth()->id());
            }])
            ->with(['season.show'])
            ->orderByDesc('last_watched_at')
            ->orderByDesc('air_timestamp')
            ->get();
    }

    #[On('episode-watched-toggled')]
    public function refreshEpisodes(): void
    {
        unset($this->episodes);
    }
}
