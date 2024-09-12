<?php

use App\Livewire\Pages;
use Illuminate\Support\Facades\Route;

Route::get('404', function() { abort(404); });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Pages\Dashboard::class)
        ->name('dashboard');

    Route::get('/search', Pages\Search::class)
        ->name('search');

    Route::get('/shows/', Pages\ShowIndex::class)
        ->name('show.index');

    Route::get('/shows/{show:external_id}/{attach?}', Pages\ShowShow::class)
        ->name('show.show')
        ->where('external_id', '[0-9]+')
        ->lazy();
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

    Route::get('/login', Pages\Auth\Login::class)
        ->name('login');

    Route::get('/register', Pages\Auth\Register::class)
        ->name('register');
});
