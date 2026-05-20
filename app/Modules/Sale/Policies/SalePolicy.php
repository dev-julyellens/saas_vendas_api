<?php

declare(strict_types=1);

namespace App\Modules\Sale\Policies;

use App\Core\Enums\SaleStatus;
use App\Models\User;
use App\Modules\Sale\Models\Sale;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sales.view')
            || $user->hasPermission('sales.manage');
    }

    public function view(User $user, Sale $sale): bool
    {
        return ($user->is_master || $user->company_id === $sale->company_id)
            && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sales.manage');
    }

    public function update(User $user, Sale $sale): bool
    {
        return ($user->is_master || $user->company_id === $sale->company_id)
            && $user->hasPermission('sales.manage')
            && ! in_array($sale->status, [SaleStatus::Confirmed, SaleStatus::Cancelled], true);
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $this->update($user, $sale);
    }

    public function confirm(User $user, Sale $sale): bool
    {
        return $this->update($user, $sale);
    }

    public function cancel(User $user, Sale $sale): bool
    {
        return $this->update($user, $sale);
    }
}
