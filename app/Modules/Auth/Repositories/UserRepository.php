<?php

namespace App\Modules\Auth\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new User);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->withoutGlobalScopes()
            ->where('email', $email)
            ->where('is_active', true)
            ->first();
    }
}
