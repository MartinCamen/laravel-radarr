<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Radarr Host
    |--------------------------------------------------------------------------
    |
    | The hostname or IP address of your Radarr server.
    |
    */
    'host' => env('RADARR_HOST', 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | Radarr Port
    |--------------------------------------------------------------------------
    |
    | The port number your Radarr server is running on.
    |
    */
    'port' => env('RADARR_PORT', 7878),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Radarr API key. You can find this in Radarr under
    | Settings > General > Security.
    |
    */
    'api_key' => env('RADARR_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Use HTTPS
    |--------------------------------------------------------------------------
    |
    | Whether to use HTTPS when connecting to Radarr.
    |
    */
    'use_https' => env('RADARR_USE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The request timeout in seconds.
    |
    */
    'timeout' => env('RADARR_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | URL Base
    |--------------------------------------------------------------------------
    |
    | The URL base for your Radarr installation if using a reverse proxy
    | with a subpath (e.g., '/radarr').
    |
    */
    'url_base' => env('RADARR_URL_BASE', ''),
];
