<?php

use App\Livewire\Pages\Search;
use Illuminate\Support\Facades\Route;

Route::get('/search', Search::class)
    ->name('search');
