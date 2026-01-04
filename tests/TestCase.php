<?php

namespace MartinCamen\LaravelRadarr\Tests;

use MartinCamen\LaravelRadarr\Facades\Radarr;
use MartinCamen\LaravelRadarr\RadarrServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            RadarrServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Radarr' => Radarr::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('radarr.host', 'localhost');
        $app['config']->set('radarr.port', 7878);
        $app['config']->set('radarr.api_key', 'test-api-key');
        $app['config']->set('radarr.use_https', false);
        $app['config']->set('radarr.timeout', 30);
        $app['config']->set('radarr.url_base', '');
    }
}
