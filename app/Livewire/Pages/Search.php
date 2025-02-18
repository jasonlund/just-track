<?php

namespace App\Livewire\Pages;

use App\Services\TVMazeService;
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

    public function boot(TVMazeService $service)
    {
        $this->service = $service;
    }

    public function render()
    {
        $results = [];

        // Check our cache for a response.
        // Since we're calling the API live, we do this so we don't hammer the API.
        // TODO -- move this to the Service.
        if ($this->query !== '') {
            $results = Cache::remember('tv-maze-search-'.$this->query, now()->addHours(3), function () {
                return $this->service->search($this->query);
            });
        }

        return view('livewire.pages.search')->with([
            'results' => Arr::map($results, function($i) {
                return array_merge([
                    'image' => $i['show']['image']['medium'] ?? null,
                    'premiered' => null,
                ], Arr::only($i['show'], [
                    'id',
                    'name',
                    'premiered'
                ]));
            }),
        ]);
    }

    public function resetQuery()
    {
        $this->query = '';
    }
}
