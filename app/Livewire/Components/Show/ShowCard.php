<?php

namespace App\Livewire\Components\Show;

use App\Models\Show;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ShowCard extends Component
{
    #[Locked]
    public Show $show;
}
