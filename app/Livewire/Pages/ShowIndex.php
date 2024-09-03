<?php

namespace App\Livewire\Pages;

use App\Models\Show;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('View Your Shows')]
class ShowIndex extends Component
{
    public Collection $shows;

    public function render()
    {
        $this->shows = auth()->user()->shows()
            ->select(['external_id', 'name', 'year'])->get();

        return view('livewire.pages.show-index');
    }
}
