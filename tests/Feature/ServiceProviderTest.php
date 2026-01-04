<?php

use MartinCamen\ArrCore\Client\RestClientInterface;
use MartinCamen\LaravelRadarr\Client\LaravelRestClient;
use MartinCamen\LaravelRadarr\Facades\Radarr;
use MartinCamen\Radarr\Config\RadarrConfiguration;
use MartinCamen\Radarr\Radarr as CoreRadarr;

it('registers the RadarrConfig singleton', function (): void {
    $radarrConfiguration = app(RadarrConfiguration::class);

    expect($radarrConfiguration)->toBeInstanceOf(RadarrConfiguration::class)
        ->and($radarrConfiguration->host)->toBe('localhost')
        ->and($radarrConfiguration->port)->toBe(7878)
        ->and($radarrConfiguration->apiKey)->toBe('test-api-key');
});

it('registers the RestClientInterface singleton', function (): void {
    $restClient = app(RestClientInterface::class);

    expect($restClient)->toBeInstanceOf(LaravelRestClient::class);
});

it('registers Core Radarr as primary singleton', function (): void {
    $radarr = app('radarr');

    expect($radarr)->toBeInstanceOf(CoreRadarr::class);
});

it('resolves Core Radarr from the facade', function (): void {
    expect(Radarr::getFacadeRoot())->toBeInstanceOf(CoreRadarr::class);
});

it('resolves Core Radarr via type hint', function (): void {
    $radarr = app(CoreRadarr::class);

    expect($radarr)->toBeInstanceOf(CoreRadarr::class);
});
