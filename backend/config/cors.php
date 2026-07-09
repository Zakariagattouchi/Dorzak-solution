<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Credentialed CORS for the first-party React SPA (Sanctum cookie mode).
    | Origins are explicit (never "*" with credentials). See docs 12 §9.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    // The SPA uses bearer-token auth (no cookies), so credential-less "*" is safe for
    // local dev. PRODUCTION must restrict this to the exact SPA origin(s) — see
    // docs/backend-planning/12-security-plan.md §9. Override via CORS_ALLOWED_ORIGINS.
    'allowed_origins' => array_filter(explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
