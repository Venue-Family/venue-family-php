<?php

namespace VenueFamily\Laravel;

use Illuminate\Support\ServiceProvider;
use VenueFamily\Laravel\Middleware\VerifyVenueFamilyWebhookSignature;
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

            $client = new VenueFamilyClient(
                apiKey: $config['api_key'] ?? null,
                organization: $config['organization'] ?? null,
                baseUrl: $config['base_url'] ?? 'https://venuefamily.com/api',
                config: [
                    'timeout' => $config['timeout'] ?? 30.0,
                ]
            );

            if (! empty($config['cache']['enabled'])) {
                $ttl = (int) ($config['cache']['ttl'] ?? 900);
                $tag = (string) ($config['cache']['tag'] ?? 'venue-family');
                $cacheStore = $app['cache']->store($config['cache']['store'] ?? null);
                $supportsTags = method_exists($cacheStore, 'supportsTags') ? $cacheStore->supportsTags() : false;

                $taggedStore = $supportsTags ? $cacheStore->tags([$tag]) : $cacheStore;

                $client = $client->withCache(
                    get: fn (string $key) => $taggedStore->get($key),
                    put: fn (string $key, array $data, int $ttl) => $taggedStore->put($key, $data, $ttl),
                    flush: function () use ($cacheStore, $supportsTags, $tag) {
                        if ($supportsTags) {
                            $cacheStore->tags([$tag])->flush();
                        }
                    },
                    ttl: $ttl
                );
            }

            return $client;
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

        if ($this->app->bound('router')) {
            $this->app['router']->aliasMiddleware(
                'venue-family.webhook',
                VerifyVenueFamilyWebhookSignature::class
            );
        }
    }
}
