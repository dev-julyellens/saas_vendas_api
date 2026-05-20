<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('saas.auth.require_email_verification', false))
        {
            return $next($request);
        }

        $user = $request->user();

        if ($user !== null && ! $user->hasVerifiedEmail() && ! $user->is_master)
        {
            return response()->json([
                'success' => false,
                'message' => 'E-mail não verificado.',
                'code' => 'EMAIL_NOT_VERIFIED',
            ], 403);
        }

        return $next($request);
    }
}
