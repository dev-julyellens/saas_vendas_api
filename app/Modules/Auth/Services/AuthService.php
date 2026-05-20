<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Auth\Concerns\LogsAuthenticationAccess;
use App\Core\Enums\AccessLogEvent;
use App\Models\User;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    use LogsAuthenticationAccess;

    public function __construct(
        private UserRepository $users,
        private BruteForceProtectionService $bruteForce,
        private SessionService $sessions,
    )
    {
    }

    /**
     * @return array{user: User, token: string, token_type: string, expires_in: int}
     */
    public function login(LoginDTO $dto, Request $request): array
    {
        if ($this->bruteForce->tooManyAttemptsForEmail($dto->email))
        {
            $this->logAccess(AccessLogEvent::LoginLocked, email: $dto->email, metadata: ['reason' => 'email_throttle']);

            throw ValidationException::withMessages([
                'email' => ['Muitas tentativas. Aguarde alguns minutos e tente novamente.'],
            ]);
        }

        $user = $this->users->findByEmailForLogin($dto->email);

        if ($user === null || ! Hash::check($dto->password, $user->password))
        {
            $this->bruteForce->recordFailedLogin($user, $dto->email);
            $this->logAccess(AccessLogEvent::LoginFailed, $user, $dto->email);

            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $this->assertUserCanAuthenticate($user);

        $token = JWTAuth::fromUser($user);
        $this->sessions->createFromToken($user, $token, $request);

        $user->forceFill(['last_login_at' => now()])->save();
        $this->bruteForce->clearForUser($user, $dto->email);
        $this->logAccess(AccessLogEvent::LoginSuccess, $user);

        return [
            'user' => $user->load('roles'),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl') * 60,
        ];
    }

    public function logout(Request $request): void
    {
        $user = auth()->user();
        $this->sessions->revokeCurrent($request);
        JWTAuth::invalidate(JWTAuth::getToken());

        if ($user instanceof User)
        {
            $this->logAccess(AccessLogEvent::Logout, $user);
        }
    }

    public function refresh(Request $request): array
    {
        $user = auth()->user();

        if (! $user instanceof User)
        {
            throw ValidationException::withMessages(['token' => ['Usuário não autenticado.']]);
        }

        $this->assertUserCanAuthenticate($user, checkPassword: false);

        $oldToken = (string) JWTAuth::getToken();
        $newToken = JWTAuth::refresh($oldToken);
        $this->sessions->rotateOnRefresh($user, $oldToken, $newToken, $request);
        $this->logAccess(AccessLogEvent::TokenRefreshed, $user);

        return [
            'token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => (int) config('jwt.ttl') * 60,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function listSessions(User $user): array
    {
        return $this->sessions->activeSessionsFor($user)
            ->map(fn($s) => [
                'id' => $s->id,
                'ip_address' => $s->ip_address,
                'user_agent' => $s->user_agent,
                'last_activity_at' => $s->last_activity_at?->toIso8601String(),
                'expires_at' => $s->expires_at?->toIso8601String(),
            ])
            ->all();
    }

    public function revokeSession(User $user, string $sessionId): void
    {
        $session = $user->sessions()
            ->where('id', $sessionId)
            ->whereNull('revoked_at')
            ->firstOrFail();

        $session->update(['revoked_at' => now()]);
        $this->logAccess(AccessLogEvent::SessionRevoked, $user, metadata: ['session_id' => $sessionId]);
    }

    public function revokeAllSessions(User $user, ?string $exceptJti = null): int
    {
        return $this->sessions->revokeAllForUser($user, $exceptJti);
    }

    private function assertUserCanAuthenticate(User $user, bool $checkPassword = true): void
    {
        if (! $user->is_active)
        {
            throw ValidationException::withMessages([
                'email' => ['Conta desativada. Entre em contato com o administrador.'],
            ]);
        }

        if ($user->isLocked())
        {
            $this->logAccess(AccessLogEvent::LoginLocked, $user);

            throw ValidationException::withMessages([
                'email' => ['Conta temporariamente bloqueada por excesso de tentativas.'],
            ]);
        }

        if (config('saas.auth.require_email_verification') && ! $user->hasVerifiedEmail())
        {
            throw ValidationException::withMessages([
                'email' => ['E-mail não verificado. Verifique sua caixa de entrada.'],
            ]);
        }
    }
}
