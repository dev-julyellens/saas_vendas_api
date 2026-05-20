<?php

/**
 * Configurações do SaaS — centraliza parâmetros de tenant e API.
 */
return [
    'api_rate_limit' => (int) env('API_RATE_LIMIT', 120),
    'default_api_version' => env('API_VERSION', 'v1'),
];
