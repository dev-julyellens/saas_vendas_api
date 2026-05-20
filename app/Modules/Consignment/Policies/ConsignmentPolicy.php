<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Policies;

use App\Models\User;
use App\Modules\Consignment\Models\Consignment;

class ConsignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('consignment.view')
            || $user->hasPermission('consignment.manage');
    }

    public function view(User $user, Consignment $consignment): bool
    {
        return ($user->is_master || $user->company_id === $consignment->company_id)
            && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('consignment.manage');
    }

    public function update(User $user, Consignment $consignment): bool
    {
        return ($user->is_master || $user->company_id === $consignment->company_id)
            && $user->hasPermission('consignment.manage')
            && ! $consignment->isClosed();
    }

    public function operate(User $user, Consignment $consignment): bool
    {
        return $this->update($user, $consignment);
    }
}
