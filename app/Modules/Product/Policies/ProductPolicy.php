<?php

namespace App\Modules\Product\Policies;

use App\Models\User;
use App\Modules\Product\Models\Product;

/**
 * Policies — autorização por recurso, complementar ao middleware de permissão.
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('products.manage');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->company_id === $product->company_id
            && $user->hasPermission('products.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('products.manage');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }
}
