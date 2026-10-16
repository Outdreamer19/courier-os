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
    | be looked up as a tenant. Tenant signup validates against this same list,
    | so it doubles as the "names a customer may not claim" list — keep any
    | hostname you might plausibly want for the platform itself in here, since
    | reclaiming one after a tenant has taken it means migrating their URL.
    |
    */

    'reserved_subdomains' => [
        // Infrastructure
        'www', 'app', 'api', 'cdn', 'assets', 'static', 'mail', 'smtp', 'ftp', 'ns1', 'ns2',
        // Platform surfaces
        'admin', 'platform', 'dashboard', 'billing', 'account', 'accounts', 'auth', 'login',
        // Content and support
        'blog', 'docs', 'help', 'support', 'status', 'careers', 'news',
        // Environments
        'staging', 'dev', 'test', 'demo', 'sandbox', 'preview', 'local',
    ],

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

    /*
    |--------------------------------------------------------------------------
    | Platform pricing
    |--------------------------------------------------------------------------
    |
    | What CourierOS charges a courier business to run on the platform. These
    | values drive the marketing pricing section, the signup summary, and the
    | MRR/ARR figures on the platform owner dashboard, so they must stay in
    | step with the Stripe prices configured in .env.
    |
    */

    'pricing' => [
        'monthly' => (float) env('COURIEROS_PRICE_MONTHLY', 79),
        'setup' => (float) env('COURIEROS_PRICE_SETUP', 349),
        'currency' => env('CASHIER_CURRENCY', 'usd') === 'usd'
            ? 'USD'
            : strtoupper((string) env('CASHIER_CURRENCY', 'USD')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform name
    |--------------------------------------------------------------------------
    |
    | What the central marketing domain calls itself in titles, share previews
    | and the shared `brand.name` prop. Kept apart from APP_NAME, which the
    | production server still carries from the Ship'd JM days and which was
    | putting "Ship'd JM" on every CourierOS link preview.
    |
    */

    'name' => env('COURIEROS_NAME', 'CourierOS'),

    /*
    |--------------------------------------------------------------------------
    | Marketing and paid ads
    |--------------------------------------------------------------------------
    |
    | sales_whatsapp: the number every "WhatsApp us" button on the marketing
    | site opens, digits only with country code.
    |
    | founding: the Founding Jamaica Cohort offer shown on /jamaica. Set
    | spots_left to 0 to hide the offer once it is gone.
    |
    | meta_pixel_id / google_tag_id: injected on the central domain only, never
    | on a tenant's white-label site. google_ads_lead: the Google Ads conversion
    | "send_to" value (AW-XXXXXXX/abcDEF) fired when someone starts a WhatsApp
    | chat or signs up.
    |
    */

    'marketing' => [
        'sales_whatsapp' => env('COURIEROS_SALES_WHATSAPP', '447565727032'),
        'founding' => [
            'setup' => (float) env('COURIEROS_FOUNDING_SETUP', 149),
            'spots_total' => (int) env('COURIEROS_FOUNDING_SPOTS', 10),
            'spots_left' => (int) env('COURIEROS_FOUNDING_SPOTS_LEFT', 10),
        ],
        'meta_pixel_id' => env('META_PIXEL_ID'),
        'google_tag_id' => env('GOOGLE_TAG_ID'),
        'google_ads_lead' => env('GOOGLE_ADS_LEAD_SEND_TO'),
    ],

];
