<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Detrás de un proxy (nginx/Caddy) que termina TLS y reenvía por HTTP
        // plano, Laravel ve la conexión interna como http:// y genera URLs
        // (route(), formularios, etc.) con ese esquema. Forzamos https cuando
        // APP_URL ya indica que el sitio público es https.
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('public-api', function ($request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('webhook', function ($request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
