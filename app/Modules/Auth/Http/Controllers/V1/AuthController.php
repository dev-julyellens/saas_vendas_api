<?php

namespace App\Modules\Auth\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Resources\UserResource;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function __construct(private AuthService $authService)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(LoginDTO::fromArray($request->validated()));

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ], 'Login realizado com sucesso.');
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->success(message: 'Logout realizado com sucesso.');
    }

    public function refresh(): JsonResponse
    {
        return $this->success($this->authService->refresh(), 'Token renovado.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()->load('roles')));
    }
}
