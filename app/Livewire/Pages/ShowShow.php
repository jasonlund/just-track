<?php

namespace App\Livewire\Pages;

use App\Models\Episode;
use App\Models\Show;
use App\Services\TMDBService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ShowShow extends Component
{
    public Show $show;
    public $episodes;

    private $service;

    public function boot(TMDBService $service)
    {
        $this->service = $service;
    }

    public function mount(Show $show, $attach = false)
    {
        if(! $show->init) {
            $data = $this->service->show($show->external_id);;
            DB::transaction(function() use(&$show, $data) {
                $show->update([
                    'name' => $data['name'],
                    'first_air_date' => $data['first_air_date'],
                    'overview' => $data['overview'],
                    'origin_country' =>  Arr::get($data['origin_country'], 0)
                ]);

//                    Episode::insert(collect($data['episodes'])->map(fn ($episode) => [
//                        'show_id' => $episode['seriesId'],
//                        'external_id' => $episode['id'],
//                        'number' => $episode['number'],
//                        'absolute_number' => $episode['absoluteNumber'],
//                        'season' => $episode['seasonNumber'],
//                        'name' => $episode['name'],
//                        'aired' => $episode['aired'],
//                        'runtime' => $episode['runtime'],
//                        'overview' => $episode['overview'],
//                    ])->toArray());
            });
        }

        if($attach !== false) {
            Auth::user()->shows()->syncWithoutDetaching([$show->id]);
            // Clear our parameter
            $this->redirectIntended(route('show.show', $show));
        }

        $this->show = $show;
        $this->episodes = collect([]);
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
