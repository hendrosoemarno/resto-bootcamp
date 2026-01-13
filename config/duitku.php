<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duitku Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Duitku Payment Gateway integration
    |
    */

    'merchant_code' => env('DUITKU_MERCHANT_CODE', ''),
    'api_key' => env('DUITKU_API_KEY', ''),
    'sandbox' => env('DUITKU_SANDBOX', true),

    'base_url' => [
        'sandbox' => 'https://sandbox.duitku.com/webapi/api/merchant/v2',
        'production' => 'https://passport.duitku.com/webapi/api/merchant/v2',
    ],

    'callback_url' => env('APP_URL') . '/api/v1/payment/duitku/callback',
    'return_url' => env('APP_URL') . '/order/payment/success',

    'payment_method' => env('DUITKU_PAYMENT_METHOD', 'SP'),

    'expiry_period' => 60, // in minutes
];
