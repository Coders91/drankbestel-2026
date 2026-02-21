<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MyParcel API
    |--------------------------------------------------------------------------
    */

    'api_key' => env('MYPARCEL_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Carrier
    |--------------------------------------------------------------------------
    |
    | PostNL carrier ID in the MyParcel system.
    |
    */

    'carrier_id' => 1, // PostNL

    /*
    |--------------------------------------------------------------------------
    | Shipment Options
    |--------------------------------------------------------------------------
    |
    | Default options applied to every shipment. These can be overridden
    | per-order by the delivery options saved at checkout.
    |
    */

    'shipment_options' => [
        'package_type' => 1, // 1 = package
        'signature' => true,
        'age_check' => true, // Required for alcohol
        'insurance' => 0, // 0 = no insurance. Options: 100, 250, 500, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 4500, 5000
        'large_format' => false,
        'only_recipient' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Label
    |--------------------------------------------------------------------------
    */

    'label' => [
        'description' => 'Bestelling #{order_number}',
        'format' => 'A4', // A4 or A6
    ],

    /*
    |--------------------------------------------------------------------------
    | Exportable Order Statuses
    |--------------------------------------------------------------------------
    |
    | Only orders with these WooCommerce statuses can be exported to MyParcel.
    |
    */

    'exportable_statuses' => [
        'processing',
        'on-hold',
    ],

];
