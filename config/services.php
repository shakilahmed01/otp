<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sslcommerz' => [
        'store_id' => env('SSLC_STORE_ID'),
        'store_password' => env('SSLC_STORE_PASSWORD'),
        'sandbox_mode' => env('SSLC_SANDBOX_MODE', true),
        'success_url' => env('SSLC_SUCCESS_URL', '/payment/success'),
        'fail_url' => env('SSLC_FAIL_URL', '/payment/fail'),
        'cancel_url' => env('SSLC_CANCEL_URL', '/payment/cancel'),
        'ipn_url' => env('SSLC_IPN_URL', '/payment/ipn'),
    ],

];
