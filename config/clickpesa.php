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

    /*
    |--------------------------------------------------------------------------
    | Pricing & Fee Configuration (.env Configurable)
    |--------------------------------------------------------------------------
    |
    | Official ClickPesa tariff rates. These can be adjusted or overridden via
    | environment variables if ClickPesa updates their fees or if custom rates
    | are negotiated for your merchant account.
    |
    */
    'pricing' => [
        // Card payments fee percentage (charged to customer)
        'card_percentage' => (float) env('CLICKPESA_FEE_CARD_PERCENT', 4.85),

        // BillPay percentage fees (charged to organization/merchant)
        'billpay_mpesa_percentage' => (float) env('CLICKPESA_FEE_BILLPAY_MPESA_PERCENT', 1.0),
        'billpay_airtel_percentage' => (float) env('CLICKPESA_FEE_BILLPAY_AIRTEL_PERCENT', 1.0),
        'billpay_halopesa_percentage' => (float) env('CLICKPESA_FEE_BILLPAY_HALOPESA_PERCENT', 2.0),
        'billpay_mixx_percentage' => (float) env('CLICKPESA_FEE_BILLPAY_MIXX_PERCENT', 2.5),
        'billpay_crdb_percentage' => (float) env('CLICKPESA_FEE_BILLPAY_CRDB_PERCENT', 1.0),

        // CRDB Direct Debit flat fee in TZS (charged to organization/merchant)
        'crdb_direct_debit_fee' => (float) env('CLICKPESA_FEE_CRDB_DIRECT_DEBIT', 2000.0),

        // TIPS TanQR / Lipa Namba collection fee percentage (charged to merchant)
        'tanqr_collection_percentage' => (float) env('CLICKPESA_FEE_TANQR_COLLECTION_PERCENT', 2.0),

        // Bank payout flat fees
        'bank_eft_fee' => (float) env('CLICKPESA_FEE_BANK_EFT', 2360.0),
        'bank_tiss_tzs_fee' => (float) env('CLICKPESA_FEE_BANK_TISS_TZS', 11800.0),
        'bank_tiss_usd_fee' => (float) env('CLICKPESA_FEE_BANK_TISS_USD', 7.50),

        // Bank EFT threshold cutoff in TZS (transfers above this use TISS)
        'bank_eft_max_threshold' => (float) env('CLICKPESA_BANK_EFT_MAX_THRESHOLD', 20000000.0),
    ],
];
