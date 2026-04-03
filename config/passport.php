<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Passport Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard Passport will use when
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'web',

    'middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    | Passport uses encryption keys while generating secure access tokens for
    | your application. By default, the keys are stored as local files but
    | can be set via environment variables when that is more convenient.
    |
    */

    'private_key' => env('PASSPORT_PRIVATE_KEY'),

    'public_key' => env('PASSPORT_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Passport Database Connection
    |--------------------------------------------------------------------------
    |
    | By default, Passport's models will utilize your application's default
    | database connection. If you wish to use a different connection you
    | may specify the configured name of the database connection here.
    |
    */

    'connection' => env('PASSPORT_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Threshold Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for various thresholds used in the application.
    | This includes settings such as the OAuth timestamp threshold,
    | which defines the acceptable time window for OAuth requests to
    | prevent replay attacks and ensure secure authentication.
    |
    */

    'threshold' => [
        'oauth-timestamp' => env('THRESHOLD_OAUTH_TIMESTAMP', 900),
    ],

    /*
    |--------------------------------------------------------------------------
    | OAuth Configuration
    |--------------------------------------------------------------------------
    |
    | OAuth settings for different services including OpenAPI and
    | File Manager integrations. These credentials are used for API
    | authentication and authorization.
    |
    */

    'oauth' => [

        /*
        |----------------------------------------------------------------------
        | OpenAPI OAuth Settings
        |----------------------------------------------------------------------
        |
        | Configuration for Self OpenAPI authentication including partner ID,
        | client key, and client secret. These credentials are required for
        | accessing Self's OpenAPI endpoints and services.
        |
        | Mostly, this is only be used for testing
        |
        */

        'openapi' => [
            'channel-id' => env('OAUTH_OPENAPI_SELF_CHANNEL_ID'),
            'partner-id' => env('OAUTH_OPENAPI_SELF_PARTNER_ID'),
            'client-key' => env('OAUTH_OPENAPI_SELF_CLIENT_KEY'),
            'client-secret' => env('OAUTH_OPENAPI_SELF_CLIENT_SECRET'),
            'ip-address' => env('OAUTH_OPENAPI_SELF_IP_ADDRESS'),
            'public-key-file' => env('OAUTH_OPENAPI_SELF_PUBLIC_KEY_FILE'),
            'private-key-file' => env('OAUTH_OPENAPI_SELF_PRIVATE_KEY_FILE'),
            'scope' => env('OAUTH_OPENAPI_SELF_SCOPE'),
        ],
    ],
];
