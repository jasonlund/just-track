<?php

namespace App\Livewire\Components\Show;

use App\Models\Show;
use App\Services\TMDBService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy]
class EpisodeList extends Component
{
    public Show $show;

    public Collection $episodes;

    private $service;

    public function boot(TMDBService $service)
    {
        $this->service = $service;
    }

    public function mount(Show $show)
    {
        $this->show = $show;
    }
}
