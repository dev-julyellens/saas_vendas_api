<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC via slug de permissão — desacoplado de rotas para reutilização em Policies.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasPermission($permission))
        {
            return response()->json([
                'success' => false,
                'message' => 'Permissão insuficiente.',
                'code' => 'FORBIDDEN',
            ], 403);
        }

        return $next($request);
    }
}
