<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Services;

use App\Models\User;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class RbacService
{
    /** @return Collection<int, Permission> */
    public function listPermissions(User $user): Collection
    {
        return Permission::query()
            ->when(! $user->is_master, fn($q) => $q->where('company_id', $user->company_id))
            ->orderBy('module')
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, Role> */
    public function listRoles(User $user): Collection
    {
        return Role::query()
            ->when(! $user->is_master, fn($q) => $q->where('company_id', $user->company_id))
            ->orderBy('name')
            ->get();
    }

    public function findRole(User $actor, string $roleId): Role
    {
        $role = Role::query()->findOrFail($roleId);

        if (! $actor->is_master && $role->company_id !== $actor->company_id)
        {
            throw ValidationException::withMessages(['role' => ['Papel não encontrado.']]);
        }

        return $role;
    }

    public function syncUserRoles(User $actor, User $target, array $roleIds): User
    {
        if (! $actor->is_master && $target->company_id !== $actor->company_id)
        {
            throw ValidationException::withMessages(['user' => ['Usuário não pertence ao tenant.']]);
        }

        $companyId = $target->company_id;

        $roles = Role::query()
            ->whereIn('id', $roleIds)
            ->when($companyId !== null, fn($q) => $q->where('company_id', $companyId))
            ->get();

        if ($roles->count() !== count($roleIds))
        {
            throw ValidationException::withMessages(['role_ids' => ['Um ou mais papéis são inválidos.']]);
        }

        $pivot = $roles->mapWithKeys(
            fn(Role $role) => [$role->id => ['company_id' => $companyId ?? $role->company_id]]
        )->all();

        $target->roles()->sync($pivot);

        return $target->fresh();
    }
}
