<?php

namespace App\Livewire\Pages;

use App\Models\Episode;
use App\Models\Show;
use App\Services\TVDBService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Js;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Search For a Show')]
class Search extends Component
{
    #[Url]
    public string $query = '';

    private $service;

    public function boot(TVDBService $service)
    {
        $this->service = $service;
    }

    public function render()
    {
        $results = [];

        // Check our cache for a response.
        // Since we're calling the TVDB API live, we do this so we don't hammer the API.
        if($this->query !== '') {
            $results = Cache::remember('tvdb-search-' . $this->query, now()->addHours(3), function () {
                return $this->service->search($this->query);
            });
        }

        return view('livewire.pages.search')->with([
            'results' =>  Arr::map($results, function($i) {
                return array_merge([        // year and thumbnail may not be set, but we want null values.
                    'year'      => null,
                    'thumbnail' => null,
                ], Arr::only($i, [
                    'tvdb_id',
                    'name',
                    'year',
                    'thumbnail',
                ]));
            })
        ]);
    }

    public function resetQuery()
    {
        $this->query = '';
    }
}
