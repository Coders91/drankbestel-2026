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

    'mailchimp' => [
        'api_key' => env('MAILCHIMP_API_KEY'),
        'list_id' => env('MAILCHIMP_LIST_ID'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

    'lightspeed' => [
        'ideal_payment_id' => env('LIGHTSPEED_IDEAL_PAYMENT_ID', 13),
        'delivery_costs_item_id' => env('LIGHTSPEED_DELIVERY_COSTS_ITEM_ID', env('LIGHTSPEED_RETAIL_API_DELIVERYCOSTS_ID')),
        'customer_id' => env('LIGHTSPEED_CUSTOMER_ID', env('LIGHTSPEED_RETAIL_API_CUSTOMER_ID')),
        'shop_id' => env('LIGHTSPEED_SHOP_ID', 1),
        'employee_id' => env('LIGHTSPEED_EMPLOYEE_ID', 1),
        'register_id' => env('LIGHTSPEED_REGISTER_ID', 1),
    ],

    'mollie' => [
        'profile_id' => env('MOLLIE_PROFILE_ID'),
        'test_mode' => env('MOLLIE_TEST_MODE'),
        'api_key' => env('MOLLIE_API_KEY'),
        'webhook_url' => env('MOLLIE_WEBHOOK_URL'),
    ],

    'myparcel' => [
        'api_key' => env('MYPARCEL_API_KEY'),
    ],

    'kadaster' => [
        'api_key' => env('BAG_KADASTER_API_KEY'),
    ]

];
