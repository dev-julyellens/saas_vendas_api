<?php

/**
 * CORS — origens permitidas via CORS_ALLOWED_ORIGINS (vírgula) ou * em local.
 * Front-end Vite padrão: http://localhost:5173 (FRONTEND_URL no .env).
 */
$origins = env('CORS_ALLOWED_ORIGINS');

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => filled($origins)
        ? array_values(array_filter(array_map('trim', explode(',', $origins))))
        : ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', false),

];
