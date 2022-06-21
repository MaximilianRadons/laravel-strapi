<?php

return [
    // The url to your Strapi installation, e.g. https://strapi.yoursite.com/
    'url' => env('STRAPI_URL'),

    // Optional, set filemanager to own url 
    'uploads_url' => env('STRAPI_UPLOADS_URL', env('STRAPI_URL')),

    // How long to cache results for in seconds
    'cache_time' => env('STRAPI_CACHE_TIME', 3600),
    'cache_prefix' => 'laravel-strapi-cache',

    // Strapi API Token created in Strapi
    'token' => env('STRAPI_API_TOKEN'),

    // Url of Webhook created in Strapi
    'webhook_url' => env('STRAPI_WEBHOOK_URL', 'strapi-cache-webhook'),

    // Signature of Webhook created in Strapi
    'webhook_signature' => env('STRAPI_WEBHOOK_SIGNATURE', 'better-than-nothing'),

    // Header Key of Signature
    'webhook_signature_header' => 'Authorization',

    // Clear cache on webhook
    'clear_cache_on_webhook' => env('STRAPI_CLEAR_CACHE_ON_WEBHOOK', true),
];
