<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Commission Rate
    |--------------------------------------------------------------------------
    |
    | The platform commission rate as a percentage.
    | For example, 15.0 means 15% commission on each order.
    |
    */
    'commission_rate' => env('MARKETPLACE_COMMISSION_RATE', 15.0),

    /*
    |--------------------------------------------------------------------------
    | Delivery Fee Settings
    |--------------------------------------------------------------------------
    |
    | Base delivery fee and per-kilometer rate for calculating delivery costs.
    |
    */
    'delivery' => [
        'base_fee' => env('MARKETPLACE_DELIVERY_BASE_FEE', 5.0),
        'per_km_rate' => env('MARKETPLACE_DELIVERY_PER_KM_RATE', 2.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rider Search Radius
    |--------------------------------------------------------------------------
    |
    | Default radius in kilometers for finding nearby riders.
    |
    */
    'rider_search_radius' => env('MARKETPLACE_RIDER_SEARCH_RADIUS', 10),
];

