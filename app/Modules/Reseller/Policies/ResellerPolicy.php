<?php

declare(strict_types=1);

namespace App\Modules\Reseller\Policies;

use App\Models\User;
use App\Modules\Reseller\Models\Reseller;

class ResellerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('resellers.manage');
    }

    public function view(User $user, Reseller $reseller): bool
    {
        return ($user->is_master || $user->company_id === $reseller->company_id)
            && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('resellers.manage');
    }

    public function update(User $user, Reseller $reseller): bool
    {
        return $this->view($user, $reseller);
    }

    public function delete(User $user, Reseller $reseller): bool
    {
        return $this->view($user, $reseller);
    }
}
