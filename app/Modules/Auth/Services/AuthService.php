<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(private UserRepository $users)
    {
    }

    /**
     * @return array{user: User, token: string, token_type: string, expires_in: int}
     */
    public function login(LoginDTO $dto): array
    {
        $user = $this->users->findByEmail($dto->email);

        if ($user === null || ! Hash::check($dto->password, $user->password))
        {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $token = JWTAuth::fromUser($user);
        $user->forceFill(['last_login_at' => now()])->save();

        return [
            'user' => $user->load('roles'),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl') * 60,
        ];
    }

    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function refresh(): array
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl') * 60,
        ];
    }
}
