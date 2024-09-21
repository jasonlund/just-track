<?php

namespace App\Livewire\Components\Show;

use App\Models\Episode;
use App\Models\Show;
use App\Services\TMDBService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class EpisodeList extends Component
{
    public Show $show;

    private $service;

    public function boot(TMDBService $service)
    {
        $this->service = $service;
    }

    public function mount(Show $show)
    {
        // If we haven't initialized the show's episodes yet, do so first.
        if($show->episodes()->count() === 0) {
            $data = $this->service->episodesBySeason(
                $show->external_id,
                $show->seasons->mapWithKeys(fn ($i) => [$i['id'] => $i['number']])
            );

            DB::transaction(function() use(&$show, $data) {
                foreach($data as $key => $episodes) {
                    foreach($episodes['episodes'] as $episode) {
                        Episode::create([
                            'season_id' => $key,
                            'external_id' => $episode['id'],
                            'number' => $episode['episode_number'],
                            'production_code' => $episode['production_code'],
                            'name' => $episode['name'],
                            'air_date' => $episode['air_date'],
                            'runtime' => $episode['runtime'],
                            'overview' => $episode['overview'],
                        ]);
                    }
                }
            });
        }

        $this->show = $show;
    }

    #[Computed]
    public function episodes()
    {
        return $this->show->episodes()
            ->with('season')
            ->get()
            ->sortBy('number')
            ->groupBy('season.number')
            ->sortBy(fn ($i) => $i->first()->seasonNumber)
            ->sortByDesc(fn ($i) => $i->first()->seasonNumber !== 0);       // Moves specials to the bottom
    }
}
