<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_master)
        {
            return response()->json([
                'success' => false,
                'message' => 'Acesso restrito ao Super Admin.',
                'code' => 'SUPER_ADMIN_REQUIRED',
            ], 403);
        }

        return $next($request);
    }
}
