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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'price_monthly' => env('STRIPE_PRICE_MONTHLY'),
        'price_setup' => env('STRIPE_PRICE_SETUP'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp (Meta Cloud API) — global fallback credentials
    |--------------------------------------------------------------------------
    |
    | Per-tenant credentials are stored on the tenants table (encrypted).
    | These env vars act as a global fallback for single-tenant / dev use.
    | Template names must match approved templates in your Meta Business
    | account. Obtain from Shane: token, phone_number_id, template names.
    |
    */
    'whatsapp' => [
        'api_token' => env('WHATSAPP_API_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'api_version' => env('WHATSAPP_API_VERSION', 'v19.0'),
        'templates' => [
            'package_status_changed' => env('WHATSAPP_TEMPLATE_PACKAGE_STATUS', 'package_status_update'),
            'package_ready_for_pickup' => env('WHATSAPP_TEMPLATE_PICKUP', 'package_ready_for_pickup'),
        ],
    ],

];
