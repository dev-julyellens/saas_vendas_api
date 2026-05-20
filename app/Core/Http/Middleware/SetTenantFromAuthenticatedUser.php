<?php

namespace App\Core\Http\Middleware;

use App\Core\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define company_id no TenantContext a partir do usuário JWT autenticado.
 * Executado após auth:api — base do isolamento multi-tenant lógico.
 */
class SetTenantFromAuthenticatedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->company_id !== null)
        {
            TenantContext::setCompanyId((string) $user->company_id);
        }

        try
        {
            return $next($request);
        }
        finally
        {
            TenantContext::reset();
        }
    }
}
