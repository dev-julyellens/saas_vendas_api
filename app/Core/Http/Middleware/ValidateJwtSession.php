<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Modules\Auth\Services\SessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Valida jti na tabela user_sessions — controle de sessão enterprise.
 */
class ValidateJwtSession
{
    public function __construct(private SessionService $sessions)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('saas.auth.validate_session', true))
        {
            return $next($request);
        }

        if ($request->user()?->is_master)
        {
            return $next($request);
        }

        if (! $this->sessions->isCurrentSessionValid())
        {
            return response()->json([
                'success' => false,
                'message' => 'Sessão inválida ou revogada.',
                'code' => 'SESSION_REVOKED',
            ], 401);
        }

        $this->sessions->touchCurrentSession($request);

        return $next($request);
    }
}
