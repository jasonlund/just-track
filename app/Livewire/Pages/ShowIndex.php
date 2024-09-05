<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('View Your Shows')]
class ShowIndex extends Component
{
    #[Computed]
    public function shows()
    {
        return auth()->user()->shows()
            ->select(['external_id', 'name', 'year'])->get();
    }
}
