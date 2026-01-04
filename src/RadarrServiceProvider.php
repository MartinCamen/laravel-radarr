<?php

namespace MartinCamen\LaravelRadarr;

use MartinCamen\LaravelRadarr\Client\LaravelRadarrRestClient;
use MartinCamen\Radarr\Client\RadarrApiClient as RadarrApi;
use MartinCamen\Radarr\Config\RadarrConfiguration;
use MartinCamen\Radarr\Radarr;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RadarrServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('radarr')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(
            RadarrConfiguration::class,
            fn($app): RadarrConfiguration => RadarrConfiguration::fromArray($app['config']->get('radarr', [])),
        );

        $this->app->singleton(
            LaravelRadarrRestClient::class,
            fn($app): LaravelRadarrRestClient => new LaravelRadarrRestClient($app->make(RadarrConfiguration::class)),
        );

        // Internal API client - not exposed via facade
        $this->app->singleton(RadarrApi::class, function ($app): RadarrApi {
            $config = $app->make(RadarrConfiguration::class);

            return new RadarrApi(
                host: $config->host,
                port: $config->port,
                apiKey: $config->apiKey,
                useHttps: $config->useHttps,
                timeout: $config->timeout,
                urlBase: $config->urlBase,
                restClient: $app->make(LaravelRadarrRestClient::class),
            );
        });

        // SDK client - the primary interface using php-arr-core domain models
        $this->app->singleton('radarr', fn($app): Radarr => new Radarr($app->make(RadarrApi::class)));

        $this->app->alias('radarr', Radarr::class);
    }
}
