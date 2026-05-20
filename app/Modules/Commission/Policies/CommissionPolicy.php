<?php

declare(strict_types=1);

namespace App\Modules\Commission\Policies;

use App\Models\User;
use App\Modules\Commission\Models\Commission;

class CommissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('commissions.manage');
    }

    public function view(User $user, Commission $commission): bool
    {
        return ($user->is_master || $user->company_id === $commission->company_id)
            && $this->viewAny($user);
    }

    public function update(User $user, Commission $commission): bool
    {
        return $this->view($user, $commission);
    }
}
