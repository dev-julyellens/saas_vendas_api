<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\Repositories\UserSessionRepository;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Controle de sessão JWT via jti — revogação e validação enterprise.
 */
class SessionService
{
    public function __construct(private UserSessionRepository $sessions)
    {
    }

    public function createFromToken(User $user, string $token, Request $request): void
    {
        $payload = JWTAuth::setToken($token)->getPayload();

        if (config('saas.auth.single_session', false))
        {
            $this->sessions->revokeAllForUser($user);
        }

        $this->sessions->create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'jti' => (string) $payload->get('jti'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes((int) config('jwt.refresh_ttl', 20160)),
        ]);
    }

    public function touchCurrentSession(Request $request): void
    {
        $jti = $this->extractJti();

        if ($jti === null)
        {
            return;
        }

        $session = $this->sessions->findActiveByJti($jti);

        if ($session !== null)
        {
            $session->update(['last_activity_at' => now()]);
        }
    }

    public function revokeCurrent(Request $request): void
    {
        $jti = $this->extractJti();

        if ($jti === null)
        {
            return;
        }

        $session = $this->sessions->findActiveByJti($jti);

        $session?->update(['revoked_at' => now()]);
    }

    public function rotateOnRefresh(User $user, string $oldToken, string $newToken, Request $request): void
    {
        $this->revokeByToken($oldToken);
        $this->createFromToken($user, $newToken, $request);
    }

    public function revokeByToken(string $token): void
    {
        try
        {
            $jti = (string) JWTAuth::setToken($token)->getPayload()->get('jti');
            $session = $this->sessions->findActiveByJti($jti);
            $session?->update(['revoked_at' => now()]);
        }
        catch (\Throwable)
        {
            // Token inválido — ignorar
        }
    }

    public function isCurrentSessionValid(): bool
    {
        if (! config('saas.auth.validate_session', true))
        {
            return true;
        }

        $jti = $this->extractJti();

        return $jti !== null && $this->sessions->findActiveByJti($jti) !== null;
    }

    /** @return \Illuminate\Support\Collection<int, \App\Modules\Auth\Models\UserSession> */
    public function activeSessionsFor(User $user): \Illuminate\Support\Collection
    {
        return $this->sessions->activeSessionsForUser($user);
    }

    public function revokeAllForUser(User $user, ?string $exceptJti = null): int
    {
        return $this->sessions->revokeAllForUser($user, $exceptJti);
    }

    private function extractJti(): ?string
    {
        try
        {
            $token = JWTAuth::getToken();

            if ($token === null)
            {
                return null;
            }

            return (string) JWTAuth::getPayload()->get('jti');
        }
        catch (\Throwable)
        {
            return null;
        }
    }
}
