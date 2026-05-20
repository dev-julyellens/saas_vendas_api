<?php

namespace App\Core\Tenant;

/**
 * Contexto de tenant em memória (request-scoped).
 *
 * Decisão arquitetural: multi-tenant LÓGICO por company_id (single database).
 * Evita schemas por tenant (custo operacional alto) e permite índices compostos
 * (company_id, id) para performance previsível em PostgreSQL.
 */
final class TenantContext
{
    private static ?string $companyId = null;

    private static bool $scopeBypass = false;

    public static function setCompanyId(?string $companyId): void
    {
        self::$companyId = $companyId;
    }

    public static function companyId(): ?string
    {
        return self::$companyId;
    }

    public static function hasCompany(): bool
    {
        return self::$companyId !== null;
    }

    /**
     * Bypass temporário para seeds, jobs cross-tenant ou super-admin.
     * Use com extremo cuidado e sempre em bloco try/finally.
     */
    public static function withoutScope(callable $callback): mixed
    {
        $previous = self::$scopeBypass;
        self::$scopeBypass = true;

        try
        {
            return $callback();
        }
        finally
        {
            self::$scopeBypass = $previous;
        }
    }

    public static function shouldBypassScope(): bool
    {
        return self::$scopeBypass;
    }

    public static function reset(): void
    {
        self::$companyId = null;
        self::$scopeBypass = false;
    }
}
