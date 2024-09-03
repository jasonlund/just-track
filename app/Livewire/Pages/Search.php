<?php

namespace App\Livewire\Pages;

use App\Services\TVDBService;
use Illuminate\Support\Arr;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Search For a Show')]
class Search extends Component
{
    #[Url]
    public string $query = '';

    public function render(TVDBService $service)
    {
        return view('livewire.pages.search')->with([
            'results' =>  $this->query !== '' ? Arr::map($service->search($this->query), function($i) {
                return Arr::only($i, [
                    'tvdb_id',
                    'name',
                    'year',
                    'thumbnail'
                ]);
            }) : []
        ]);
    }
}
