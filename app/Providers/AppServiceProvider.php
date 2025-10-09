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
        // Ensure Pusher uses the correct domain even if config cache is stale
        if (config('broadcasting.default') === 'pusher') {
            $cluster = env('PUSHER_APP_CLUSTER', 'mt1');

            config([
                'broadcasting.connections.pusher.options.host' => 'api-' . $cluster . '.pusher.com',
                'broadcasting.connections.pusher.options.port' => 443,
                'broadcasting.connections.pusher.options.scheme' => 'https',
                'broadcasting.connections.pusher.options.encrypted' => true,
                'broadcasting.connections.pusher.options.useTLS' => true,
            ]);
        }
    }
}
