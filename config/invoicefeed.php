<?php

return [

    'api_url' => rtrim(env('INVOICEFEED_API_URL', 'https://invoicefeed.com/api/v1'), '/'),

    'api_token' => env('INVOICEFEED_API_TOKEN'),

    'enabled' => (bool) env('INVOICEFEED_ENABLED', true),

    'webhook_secret' => env('INVOICEFEED_WEBHOOK_SECRET'),

];
