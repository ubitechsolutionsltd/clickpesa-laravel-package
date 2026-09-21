<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ClickPesa Credentials
    |--------------------------------------------------------------------------
    |
    | Retrieve these from your ClickPesa Merchant Dashboard:
    | Settings -> Developers -> Applications.
    |
    */
    'client_id' => env('CLICKPESA_CLIENT_ID'),
    'api_key' => env('CLICKPESA_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Checksum Signing & Verification
    |--------------------------------------------------------------------------
    |
    | If enabled in your ClickPesa application settings, provide your checksum
    | secret key here. The SDK will automatically sign outgoing requests
    | and verify incoming webhook callbacks.
    |
    */
    'checksum_key' => env('CLICKPESA_CHECKSUM_KEY'),
    'checksum_enabled' => env('CLICKPESA_CHECKSUM_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | API Base URL & Timeouts
    |--------------------------------------------------------------------------
    |
    | Defaults to ClickPesa production third-parties API endpoint.
    |
    */
    'base_url' => env('CLICKPESA_BASE_URL', 'https://api.clickpesa.com/third-parties'),
    'timeout' => (int) env('CLICKPESA_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Token Cache Settings
    |--------------------------------------------------------------------------
    |
    | JWT tokens are valid for 1 hour. Set the safety margin in seconds before
    | expiration when the token should be proactively refreshed (default: 300s = 5m).
    |
    */
    'token_ttl_margin' => (int) env('CLICKPESA_TOKEN_TTL_MARGIN', 300),

    /*
    |--------------------------------------------------------------------------
    | Webhooks Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the URL prefix or middleware used by the `Route::clickpesaWebhooks()`
    | macro.
    |
    */
    'webhooks' => [
        'prefix' => env('CLICKPESA_WEBHOOK_PREFIX', 'clickpesa/webhooks'),
        'middleware' => ['api'],
    ],
];
