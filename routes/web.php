<?php

use App\Http\Controllers\ImageStreamController;
use App\Livewire\Pages;
use Illuminate\Support\Facades\Route;

Route::get('404', function () {
    abort(404);
});

Route::get('/images/art/{path}', ImageStreamController::class)
    ->where('path', '.*')
    ->name('images.stream');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Pages\Dashboard::class)
        ->name('dashboard');

    Route::get('/dashboard/upcoming', Pages\DashboardUpcoming::class)
        ->name('dashboard.upcoming');

    Route::get('/search', Pages\Search::class)
        ->name('search');

    Route::get('/shows', Pages\ShowIndex::class)
        ->name('show.index');

    Route::get('/shows/{show}/{attach?}', Pages\ShowShow::class)
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
