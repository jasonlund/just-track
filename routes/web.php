<?php

use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Search;
use App\Livewire\Pages\ShowIndex;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)
        ->name('dashboard');

    Route::get('/search', Search::class)
        ->name('search');

    Route::get('/shows/', ShowIndex::class)
        ->name('show.index');
});



Route::middleware('guest')->group(function () {
//    Volt::route('register', 'pages.auth.register')
//        ->name('register');
//
//    Volt::route('login', 'pages.auth.login')
//        ->name('login');
//
//    Volt::route('forgot-password', 'pages.auth.forgot-password')
//        ->name('password.request');
//
//    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
//        ->name('password.reset');

    Route::get('/login', Login::class)
        ->name('login');
});
