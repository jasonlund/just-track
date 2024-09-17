<?php

namespace App\Livewire\Pages;

use App\Models\Season;
use App\Models\Show;
use App\Services\TMDBService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ShowShow extends Component
{
    public Show $show;

    private $service;

    public function boot(TMDBService $service)
    {
        $this->service = $service;
    }

    public function mount(Show $show, $attach = false)
    {
        if(! $show->init) {
            $data = $this->service->show($show->external_id);

            DB::transaction(function() use(&$show, $data) {
                $show->update([
                    'name' => $data['name'],
                    'status' => strtolower($data['status']),
                    'first_air_date' => $data['first_air_date'],
                    'overview' => $data['overview'],
                    'origin_country' =>  Arr::get($data['origin_country'], 0)
                ]);

                Season::insert(collect($data['seasons'])->map(fn ($season)  => [
                    'show_id' => $show->id,
                    'external_id' => $season['id'],
                    'number' => $season['id'],
                    'air_date' => $season['air_date'],
                    'name' => $season['name'] === '' ? null : $season['name'],
                    'overview' => $season['overview'] === '' ? null : $season['overview'],
                ])->toArray());
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
