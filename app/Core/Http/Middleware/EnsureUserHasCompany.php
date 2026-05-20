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

        if ($user === null)
        {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado.',
                'code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Super Admin opera fora do tenant — rotas de plataforma usam middleware super-admin
        if ($user->is_master || $user->company_id !== null)
        {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Usuário sem empresa vinculada.',
            'code' => 'TENANT_NOT_ASSIGNED',
        ], 403);
    }
}
