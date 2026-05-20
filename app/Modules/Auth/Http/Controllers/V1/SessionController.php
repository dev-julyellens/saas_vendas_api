<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends ApiController
{
    public function __construct(private AuthService $authService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->authService->listSessions($request->user()));
    }

    public function destroy(Request $request, string $sessionId): JsonResponse
    {
        $this->authService->revokeSession($request->user(), $sessionId);

        return $this->success(message: 'Sessão revogada.');
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $count = $this->authService->revokeAllSessions($request->user());

        return $this->success(['revoked' => $count], 'Todas as sessões foram revogadas.');
    }
}
