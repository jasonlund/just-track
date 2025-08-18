<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
        // Configure global User-Agent for all HTTP requests
        Http::globalRequestMiddleware(
            fn ($request) => $request->withHeader(
                'User-Agent', config('services.user_agent')
            )
        );

        // Eager load shows only when retrieving the user from session
        Auth::getProvider()->withQuery(function ($query) {
            return $query->with('shows:id');
        });
    }
}
