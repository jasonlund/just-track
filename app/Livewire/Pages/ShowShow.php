<?php

namespace App\Livewire\Pages;

use App\Models\Show;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class ShowShow extends Component
{
    public Show $show;
    public Collection $episodes;

    public function mount(Show $show)
    {
        $this->show = $show;
        $this->episodes = $show->episodes()->critical()->get();
    }
}
