<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Policies;

use App\Models\User;
use App\Modules\Rbac\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('roles.manage');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.manage')
            && ($user->is_master || $user->company_id === $role->company_id);
    }
}
