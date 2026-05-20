<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia acesso de usuários sem empresa vinculada (exceto rotas públicas de auth).
 */
class EnsureUserHasCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->company_id === null)
        {
            return response()->json([
                'success' => false,
                'message' => 'Usuário sem empresa vinculada.',
                'code' => 'TENANT_NOT_ASSIGNED',
            ], 403);
        }

        return $next($request);
    }
}
