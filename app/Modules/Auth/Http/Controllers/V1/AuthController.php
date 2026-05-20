<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Auth\DTOs\ForgotPasswordDTO;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\DTOs\ResetPasswordDTO;
use App\Modules\Auth\Http\Requests\ForgotPasswordRequest;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\ResetPasswordRequest;
use App\Modules\Auth\Http\Resources\UserResource;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Services\EmailVerificationService;
use App\Modules\Auth\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function __construct(
        private AuthService $authService,
        private PasswordResetService $passwordReset,
        private EmailVerificationService $emailVerification,
    )
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(LoginDTO::fromArray($request->validated()), $request);

        return $this->success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ], 'Login realizado com sucesso.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);

        return $this->success(message: 'Logout realizado com sucesso.');
    }

    public function refresh(Request $request): JsonResponse
    {
        return $this->success($this->authService->refresh($request), 'Token renovado.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles.permissions');

        return $this->success(new UserResource($user));
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->passwordReset->sendResetLink(ForgotPasswordDTO::fromArray($request->validated()));

        return $this->success(message: 'Se o e-mail existir, enviaremos instruções de recuperação.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->passwordReset->reset(ResetPasswordDTO::fromArray([
            'email' => $request->input('email'),
            'token' => $request->input('token'),
            'password' => $request->input('password'),
            'passwordConfirmation' => $request->input('password_confirmation'),
        ]));

        return $this->success(message: 'Senha redefinida com sucesso.');
    }

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $this->emailVerification->sendNotification($request->user());

        return $this->success(message: 'Link de verificação enviado.');
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        $verified = $this->emailVerification->verifyFromLink($request, $id, $hash);

        if (! $verified)
        {
            return $this->error('Link de verificação inválido ou expirado.', 403, code: 'INVALID_VERIFICATION_LINK');
        }

        return $this->success(message: 'E-mail verificado com sucesso.');
    }
}
