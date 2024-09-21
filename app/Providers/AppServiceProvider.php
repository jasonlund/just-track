<?php

namespace App\Providers;

use App\Models\Show;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('show', function (string $value) {
            // Our show is already loaded if our user has it attached, so prefer that over a db call.
            if($show = auth()->user()->shows->where('external_id', $value)->first()) return $show;
            return Show::where('external_id', $value)->first();
        });
    }
}
