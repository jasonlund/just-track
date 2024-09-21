<?php

namespace App\Livewire\Pages;

use App\Services\TMDBService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Search For a Show')]
class Search extends Component
{
    #[Url]
    public string $query = '';

    private $service;

    public function boot(TMDBService $service)
    {
        $this->service = $service;
    }

    public function render()
    {
        $results = [];

        // Check our cache for a response.
        // Since we're calling the TMDB API live, we do this so we don't hammer the API.
        if ($this->query !== '') {
            $results = Cache::remember('tmdb-search-'.$this->query, now()->addHours(3), function () {
                return $this->service->search($this->query);
            });
        }

        return view('livewire.pages.search')->with([
            'results' => Arr::map($results, function ($i) {
                return array_merge([
                    'first_air_date' => null,
                    'poster_path' => null,
                ], Arr::only($i, [
                    'id',
                    'name',
                    'first_air_date',
                    'poster_path',
                ]));
            }),
        ]);
    }

    public function resetQuery()
    {
        $this->query = '';
    }
}
