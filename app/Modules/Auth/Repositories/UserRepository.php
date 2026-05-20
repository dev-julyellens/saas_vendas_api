<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new User);
    }

    public function findByEmail(string $email, bool $onlyActive = false): ?User
    {
        return User::query()
            ->withoutGlobalScopes()
            ->where('email', $email)
            ->when($onlyActive, fn($q) => $q->where('is_active', true))
            ->first();
    }

    public function findByEmailForLogin(string $email): ?User
    {
        return $this->findByEmail($email);
    }

    public function incrementFailedAttempts(User $user): void
    {
        $user->increment('failed_login_attempts');
    }

    public function resetFailedAttempts(User $user): void
    {
        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();
    }

    public function lockUntil(User $user, \DateTimeInterface $until): void
    {
        $user->forceFill(['locked_until' => $until])->save();
    }
}
