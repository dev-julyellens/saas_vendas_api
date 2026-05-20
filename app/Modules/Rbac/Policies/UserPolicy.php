<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->hasPermission('users.manage')
            && ($actor->is_master || $actor->company_id === $target->company_id);
    }

    public function update(User $actor, User $target): bool
    {
        return $this->view($actor, $target);
    }

    public function assignRoles(User $actor, User $target): bool
    {
        return $this->view($actor, $target);
    }
}
