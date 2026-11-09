<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Una sola lettura dei dati dell'azienda per richiesta (e per ogni test).
        $this->app->singleton(\App\Services\DatiAzienda::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn () => Password::min(10)->mixedCase()->numbers());

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
