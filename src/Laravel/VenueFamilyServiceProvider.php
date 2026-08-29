<?php

namespace VenueFamily\Laravel;

use Illuminate\Support\ServiceProvider;
use VenueFamily\VenueFamilyClient;

class VenueFamilyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/venue-family.php',
            'venue-family'
        );

        $this->app->singleton(VenueFamilyClient::class, function ($app) {
            $config = $app['config']->get('venue-family', []);

            return new VenueFamilyClient(
                apiKey: $config['api_key'] ?? null,
                organization: $config['organization'] ?? null,
                baseUrl: $config['base_url'] ?? 'https://venuefamily.com/api',
                config: [
                    'timeout' => $config['timeout'] ?? 30.0,
                ]
            );
        });

        $this->app->alias(VenueFamilyClient::class, 'venue-family');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/venue-family.php' => $this->app->configPath('venue-family.php'),
            ], 'venue-family-config');
        }
    }
}
