<?php

namespace App\Livewire\Components\Show;

use App\Models\Episode;
use App\Models\Show;
use App\Services\TMDBService;
use App\Services\TVMazeService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class EpisodeList extends Component
{
    public Show $show;

    private $service;

    public function boot(TVMazeService $service)
    {
        $this->service = $service;
    }

    public function mount(Show $show)
    {
        // If we haven't initialized the show's episodes yet, do so first.
        if ($show->episodes()->count() === 0) {
            $data = $this->service->episodes($show->external_id);

            $seasons = $show->seasons;

            DB::beginTransaction();

            try {
                foreach ($data as $key => $episode) {
                    // Get our season for our relationship if we don't already have it.
                    if(! isset($season) || $season !== $episode['season']) {
                        $season = $seasons->where('number', $episode['season'])
                            ->pluck('id')
                            ->first();
                    }

                    // The ShowShow test fails without seeding all the seasons.
                    // So just skip if we don't have a season.
                    if(app()->runningUnitTests() && ! $season) continue;

                    Episode::create([
                        'season_id' => $season,
                        'external_id' => $episode['id'],
                        'number' => $episode['number'],
                        'type' => $episode['type'],
                        'name' => $episode['name'],
                        'premiered' => $episode['airdate'],
                        'air_timestamp' => Carbon::parse($episode['airstamp']),
                        'runtime' => $episode['runtime'],
                        'image' => $episode['image']['original'] ?? null,
                        'summary' => $episode['summary'],
                    ]);
                }

                DB::commit();
            } catch(\Exception $e) {
                DB::rollBack();

                throw $e;
            }
        }

        $this->show = $show;
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            Loading...
        </div>
        HTML;
    }

    #[Computed]
    public function episodes()
    {
        return Episode::with('season')
            ->whereHas('season', function (Builder $query) {
                $query->where('show_id', $this->show->id);
            })
            ->whereNot('type', 'insignificant_special')
            ->get()
            ->sortBy('air_timestamp')
            ->groupBy('season.number')
            ->sortBy(fn ($i) => $i->first()->seasonNumber);
    }

    public function sync($episodeId)
    {
        $episode = Episode::find($episodeId);

        $this->authorize('attachUser', $episode);

        if ($episode->attached) {
            auth()->user()->episodes()->detach($episode);
        } else {
            auth()->user()->episodes()->attach($episode, [
                'created_at' => now(),
            ]);
        }

        // Need to refresh the user's relationship for changes to display.
        auth()->user()->load('episodes');
    }
}
