<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Primary display currency for shipping rates, package charges, and
    | customer-facing payment amounts. Stored alongside numeric values so
    | future multi-currency support is straightforward.
    |
    */

    'currency' => env('SHIPDJM_CURRENCY', 'JMD'),

    /*
    |--------------------------------------------------------------------------
    | Default shipping rate
    |--------------------------------------------------------------------------
    |
    | Fallback rate per pound used only when no active ShippingRate exists
    | in the database. Real rates should be managed through the admin
    | portal so weight tiers and minimum charges can be tuned later.
    |
    */

    'default_rate_per_lb' => (float) env('SHIPDJM_DEFAULT_RATE_PER_LB', 500),

    /*
    |--------------------------------------------------------------------------
    | Customer reference format
    |--------------------------------------------------------------------------
    |
    | Customer reference numbers look like SJM-000001. The prefix and the
    | zero-padding width are configurable so the format can evolve without
    | a code change.
    |
    */

    'customer_reference' => [
        'prefix' => env('SHIPDJM_CUSTOMER_REFERENCE_PREFIX', 'SJM'),
        'padding' => (int) env('SHIPDJM_CUSTOMER_REFERENCE_PADDING', 6),
        'random_length' => (int) env('SHIPDJM_CUSTOMER_REFERENCE_RANDOM_LENGTH', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Package reference format
    |--------------------------------------------------------------------------
    */

    'package_reference' => [
        'prefix' => env('SHIPDJM_PACKAGE_REFERENCE_PREFIX', 'PKG'),
        'padding' => (int) env('SHIPDJM_PACKAGE_REFERENCE_PADDING', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice uploads
    |--------------------------------------------------------------------------
    |
    | Accepted MIME types and size limit for pre-alert invoice/receipt
    | uploads. Files are stored on the `local` (private) disk and served
    | through signed routes only.
    |
    */

    'whatsapp' => [
        'default_country_code' => env('SHIPDJM_WHATSAPP_COUNTRY_CODE', '1'),
    ],

    'invoice_uploads' => [
        'disk' => 'local',
        'directory' => 'invoices',
        'max_kilobytes' => 8192,
        'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],
    ],

];
