<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_active)
        {
            return response()->json([
                'success' => false,
                'message' => 'Conta desativada.',
                'code' => 'ACCOUNT_DISABLED',
            ], 403);
        }

        return $next($request);
    }
}
