<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Venue Family API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the Venue Family instance you are communicating with.
    | Defaults to the production URL. For local dev or staging, configure
    | this in your .env file.
    |
    */
    'base_url' => env('VENUE_FAMILY_BASE_URL', 'https://venuefamily.com/api'),

    /*
    |--------------------------------------------------------------------------
    | Organization API Token / Personal Access Token
    |--------------------------------------------------------------------------
    |
    | Your Venue Family API Token (obtained in Settings -> API in your
    | organization dashboard). Required for authenticated endpoints.
    |
    */
    'api_key' => env('VENUE_FAMILY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Organization Slug
    |--------------------------------------------------------------------------
    |
    | The default organization slug used for scoped endpoints (e.g. 'the-418-project').
    |
    */
    'organization' => env('VENUE_FAMILY_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Webhook & Postback Signing Secret
    |--------------------------------------------------------------------------
    |
    | The secret key used to verify incoming webhook signatures (HMAC-SHA256)
    | or sign conversion tracking postbacks.
    |
    */
    'webhook_secret' => env('VENUE_FAMILY_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The HTTP client timeout in seconds for API calls.
    |
    */
    'timeout' => (float) env('VENUE_FAMILY_TIMEOUT', 30.0),

    /*
    |--------------------------------------------------------------------------
    | API Response Caching
    |--------------------------------------------------------------------------
    |
    | When enabled, GET responses from the Venue Family API will be cached
    | using your application's default cache store or specified store.
    |
    */
    'cache' => [
        'enabled' => (bool) env('VENUE_FAMILY_CACHE_ENABLED', false),
        'ttl' => (int) env('VENUE_FAMILY_CACHE_TTL', 900),
        'tag' => env('VENUE_FAMILY_CACHE_TAG', 'venue-family'),
        'store' => env('VENUE_FAMILY_CACHE_STORE'),
    ],

];
