<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Models\User;
use App\Modules\Auth\Models\UserSession;
use Illuminate\Support\Collection;

class UserSessionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new UserSession);
    }

    public function findActiveByJti(string $jti): ?UserSession
    {
        return UserSession::query()
            ->where('jti', $jti)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function revokeAllForUser(User $user, ?string $exceptJti = null): int
    {
        return UserSession::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->when($exceptJti !== null, fn($q) => $q->where('jti', '!=', $exceptJti))
            ->update(['revoked_at' => now()]);
    }

    /** @return Collection<int, UserSession> */
    public function activeSessionsForUser(User $user): Collection
    {
        return UserSession::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('last_activity_at')
            ->get();
    }
}
