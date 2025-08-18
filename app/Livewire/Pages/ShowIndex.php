<?php

namespace App\Livewire\Pages;

use App\Models\Show;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('View Your Shows')]
class ShowIndex extends Component
{
    #[Computed]
    public function shows()
    {
        return Show::whereIn('id', auth()->user()->shows->pluck('id'))->get();
    }
}
