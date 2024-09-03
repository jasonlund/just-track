<?php

use Illuminate\Support\Facades\Route;

Route::get('/search', \App\Livewire\Pages\Search::class)
    ->name('search');

Route::get('/shows/', \App\Livewire\Pages\ShowIndex::class)
    ->name('show.index');
