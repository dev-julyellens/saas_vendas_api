<?php

namespace App\Core\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Respostas de erro JSON padronizadas — nunca expõe stack trace em produção.
 */
class ApiExceptionHandler
{
    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->expectsJson() && ! $request->is('api/*'))
        {
            return null;
        }

        return match (true)
        {
            $e instanceof ValidationException => response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'code' => 'VALIDATION_ERROR',
                'errors' => $e->errors(),
            ], 422),

            $e instanceof AuthenticationException => response()->json([
                'success' => false,
                'message' => 'Não autenticado.',
                'code' => 'UNAUTHENTICATED',
            ], 401),

            $e instanceof AuthorizationException => response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Acesso negado.',
                'code' => 'FORBIDDEN',
            ], 403),

            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => response()->json([
                'success' => false,
                'message' => 'Recurso não encontrado.',
                'code' => 'NOT_FOUND',
            ], 404),

            $e instanceof HttpException => response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Erro HTTP.',
                'code' => 'HTTP_ERROR',
            ], $e->getStatusCode()),

            default => response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor.',
                'code' => 'SERVER_ERROR',
            ], 500),
        };
    }
}
