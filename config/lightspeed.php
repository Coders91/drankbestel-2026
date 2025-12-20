<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the product mapping cache that stores WooCommerce to
    | Lightspeed product relationships.
    |
    */

    'cache' => [
        'ttl' => env('LIGHTSPEED_CACHE_TTL', 3600),
        'key_prefix' => 'lightspeed',
        'store' => env('LIGHTSPEED_CACHE_STORE', 'file'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the sync process between Lightspeed and WooCommerce.
    |
    */

    'sync' => [
        'batch_size' => env('LIGHTSPEED_SYNC_BATCH_SIZE', 100),
        'enabled_strategies' => ['stock'],
    ],

    /*
    |--------------------------------------------------------------------------
    | WooCommerce Configuration
    |--------------------------------------------------------------------------
    |
    | Settings related to WooCommerce integration.
    |
    */

    'woocommerce' => [
        'lightspeed_id_meta_key' => 'lightspeed_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cron Security
    |--------------------------------------------------------------------------
    |
    | Secret key for authenticating cron requests.
    |
    */

    'cron_secret' => env('LIGHTSPEED_CRON_SECRET', ''),
];
