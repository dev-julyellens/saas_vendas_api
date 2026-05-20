<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
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

        foreach ($roles as $role)
        {
            if ($user->hasRole($role))
            {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Perfil insuficiente.',
            'code' => 'FORBIDDEN',
        ], 403);
    }
}
