<?php

use App\Livewire\Pages\Dashboard;
use Livewire\Livewire;
use function Pest\Laravel\get;

it("renders successfully", function () {
    asUser();

    get(route('dashboard'))
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class);
});
