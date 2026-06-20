<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Central domain
    |--------------------------------------------------------------------------
    |
    | The apex domain that hosts the CourierOS marketing site, tenant signup,
    | and platform super-admin. Requests to this domain (and the reserved
    | subdomains below) are treated as "central" and are NOT scoped to any
    | tenant. Tenants live on {subdomain}.{central_domain}.
    |
    */

    'central_domain' => env('COURIEROS_CENTRAL_DOMAIN', 'courieros.co'),

    /*
    |--------------------------------------------------------------------------
    | Reserved subdomains
    |--------------------------------------------------------------------------
    |
    | Subdomains that should always resolve to the central context rather than
    | be looked up as a tenant.
    |
    */

    'reserved_subdomains' => ['www', 'app', 'admin', 'api', 'mail', 'staging'],

    /*
    |--------------------------------------------------------------------------
    | Platform fallback defaults
    |--------------------------------------------------------------------------
    |
    | Used when no tenant is bound, or as the default for a new tenant that has
    | not configured its own values yet.
    |
    */

    'currency' => env('COURIEROS_DEFAULT_CURRENCY', 'USD'),

    'default_rate_per_lb' => env('COURIEROS_DEFAULT_RATE_PER_LB', 500),

];
