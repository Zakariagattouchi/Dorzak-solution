<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Comparator carrier vocabulary
    |--------------------------------------------------------------------------
    | Codes the delivery system accepts for `comparator_quotes.*.provider`.
    | Adding a new third-party carrier means adding it here AND to the delivery
    | system's provider enum — it rejects codes it doesn't know.
    */
    'comparator_codes' => ['uber', 'snoonu'],

    /*
    |--------------------------------------------------------------------------
    | Trip defaults sent with every quote
    |--------------------------------------------------------------------------
    | The delivery system prices per vehicle class and pricing zone. Merchant
    | orders are small parcels, so we ask for the cheapest class by default.
    */
    'vehicle_requirement' => env('DELIVERY_VEHICLE_REQUIREMENT', 'any'),
    'zone_key' => env('DELIVERY_ZONE_KEY', 'doha'),
    'comparator_tier' => env('DELIVERY_COMPARATOR_TIER', 'bike'),

    /*
    |--------------------------------------------------------------------------
    | Dorzak Business (the delivery system)
    |--------------------------------------------------------------------------
    | Ports the main-Dorzak integration: client-credentials token, exact-body
    | HMAC signing, idempotent mutations. When disabled or unreachable the
    | Dorzak courier is simply hidden from checkout — we never show a price the
    | network never quoted.
    */
    'business' => [
        'enabled' => env('DORZAK_BUSINESS_ENABLED', false),
        'base_url' => rtrim((string) env('DORZAK_BUSINESS_URL', 'http://127.0.0.1:8006'), '/'),
        'client_id' => env('DORZAK_BUSINESS_CLIENT_ID'),
        'client_secret' => env('DORZAK_BUSINESS_CLIENT_SECRET'),
        'webhook_secret' => env('DORZAK_BUSINESS_WEBHOOK_SECRET'),
        'timeout' => (int) env('DORZAK_BUSINESS_TIMEOUT', 8),
        'source_system' => env('DORZAK_BUSINESS_SOURCE_SYSTEM', 'dorzak-merchant'),
    ],
];
