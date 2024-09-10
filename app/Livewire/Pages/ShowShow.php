<?php

namespace App\Livewire\Pages;

use App\Models\Episode;
use App\Models\Show;
use App\Services\TVDBService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ShowShow extends Component
{
    public Show $show;
    public Collection $episodes;

    private $service;

    public function boot(TVDBService $service)
    {
        $this->service = $service;
    }

    public function mount($tvdb_id, $attach = false)
    {
        // We might not have this show in our database yet, so grab it if we don't.
        if(! $show = Show::where('external_id', $tvdb_id)->first()) {
            try {
                // Check our cache for a response.
                // Since we're calling the TVDB API live, we do this so we don't hammer the API.
                $data = Cache::remember('tvdb-show-' . $tvdb_id, now()->addHours(3), function () use($tvdb_id) {
                    return $this->service->episodes($tvdb_id);
                });

                // If we've cached false that means we got an exception before.
                // Exit the mount and we'll redirect in our component.
                if($data === false) return;

                DB::transaction(function() use(&$show, $data) {
                    $show = Show::create([
                        'external_id' => $data['series']['id'],
                        'name' => $data['series']['name'],
                        'year' => $data['series']['year'],
                        'overview' => $data['series']['overview'],
                        'original_country' => $data['series']['originalCountry'],
                    ]);

                    Episode::insert(collect($data['episodes'])->map(fn ($episode) => [
                        'show_id' => $episode['seriesId'],
                        'external_id' => $episode['id'],
                        'number' => $episode['number'],
                        'absolute_number' => $episode['absoluteNumber'],
                        'season' => $episode['seasonNumber'],
                        'name' => $episode['name'],
                        'aired' => $episode['aired'],
                        'runtime' => $episode['runtime'],
                        'overview' => $episode['overview'],
                    ])->toArray());
                });
            }catch(RequestException $e) {
                Cache::put('tvdb-show-' . $tvdb_id, false, now()->addHours(3));
                Log::error($e);
                return;
            }
        }

        if($attach !== false) {
            Auth::user()->shows()->syncWithoutDetaching([$show->id]);
            // Clear our parameter
            $this->redirectIntended(route('show.show', $show));
        }

        $this->show = $show;
        $this->episodes = $this->show->episodes()->critical()->get();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            Loading...
        </div>
        HTML;
    }
}
