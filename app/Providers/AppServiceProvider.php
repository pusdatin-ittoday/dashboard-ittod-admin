<?php

namespace App\Providers;

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
        if (str_starts_with(config('app.url'), 'https://') && !in_array(request()->getHost(), ['localhost', '127.0.0.1'], true)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
