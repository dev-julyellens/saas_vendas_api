<?php

/**
 * Configurações do SaaS — tenant, API e segurança de autenticação.
 */
return [
    'api_rate_limit' => (int) env('API_RATE_LIMIT', 120),
    'default_api_version' => env('API_VERSION', 'v1'),

    'auth' => [
        /** Exige e-mail verificado para login e rotas protegidas. */
        'require_email_verification' => (bool) env('AUTH_REQUIRE_EMAIL_VERIFICATION', false),

        /** Tentativas de login antes de bloqueio temporário da conta. */
        'max_login_attempts' => (int) env('AUTH_MAX_LOGIN_ATTEMPTS', 5),

        /** Minutos de bloqueio após exceder tentativas. */
        'lockout_minutes' => (int) env('AUTH_LOCKOUT_MINUTES', 15),

        /** Tentativas por e-mail (cache) além do throttle por IP. */
        'max_attempts_per_email' => (int) env('AUTH_MAX_ATTEMPTS_PER_EMAIL', 5),

        /** Janela em minutos para contagem de tentativas por e-mail. */
        'email_attempt_decay_minutes' => (int) env('AUTH_EMAIL_ATTEMPT_DECAY', 15),

        /** Revogar demais sessões ativas ao fazer login (sessão única). */
        'single_session' => (bool) env('AUTH_SINGLE_SESSION', false),

        /** Validar jti na tabela user_sessions em cada request autenticado. */
        'validate_session' => (bool) env('AUTH_VALIDATE_SESSION', true),
    ],
];
