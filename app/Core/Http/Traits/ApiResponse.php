<?php

namespace App\Core\Http\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Envelope JSON padronizado — facilita consumo por clientes mobile/SPA
 * e versionamento consistente da API.
 */
trait ApiResponse
{
    protected function success(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        array $meta = []
    ): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta ?: (object) [],
        ], $status);
    }

    protected function created(mixed $data = null, string $message = 'Recurso criado com sucesso.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function error(
        string $message,
        int $status = 400,
        mixed $errors = null,
        string $code = 'ERROR'
    ): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'errors' => $errors,
        ], $status);
    }
}
