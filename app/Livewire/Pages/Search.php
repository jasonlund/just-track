<?php

namespace App\Livewire\Pages;

use App\Services\TVDBService;
use Illuminate\Support\Arr;
use Livewire\Attributes\Url;
use Livewire\Component;


class Search extends Component
{
    #[Url]
    public string $query = '';

    public array $results = [];

    public function render(TVDBService $service)
    {
        $this->results = $this->query !== '' ? Arr::map($service->search($this->query), function($i) {
            return Arr::only($i, [
                'tvdb_id',
                'name',
                'year',
                'thumbnail'
            ]);
        }) : [];
        return view('livewire.pages.search');
    }
}
