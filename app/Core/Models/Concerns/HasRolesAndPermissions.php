<?php

declare(strict_types=1);

namespace App\Core\Models\Concerns;

use App\Core\Enums\RoleSlug;
use App\Core\Tenant\TenantContext;
use App\Modules\Rbac\Models\Role;

/**
 * RBAC por tenant — Super Admin via is_master (bypass em Gate::before).
 */
trait HasRolesAndPermissions
{
    public function hasPermission(string $slug): bool
    {
        if ($this->is_master ?? false)
        {
            return true;
        }

        $companyId = $this->company_id ?? TenantContext::companyId();

        return $this->roles()
            ->when($companyId !== null, fn($q) => $q->where('roles.company_id', $companyId))
            ->whereHas(
                'permissions',
                fn($q) => $q->where('slug', $slug)
                    ->when($companyId !== null, fn($q2) => $q2->where('permissions.company_id', $companyId))
            )
            ->exists();
    }

    public function hasRole(string $slug): bool
    {
        if ($this->is_master ?? false)
        {
            return true;
        }

        $companyId = $this->company_id ?? TenantContext::companyId();

        return $this->roles()
            ->when($companyId !== null, fn($q) => $q->where('roles.company_id', $companyId))
            ->where('slug', $slug)
            ->exists();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) ($this->is_master ?? false);
    }

    public function isEmpresa(): bool
    {
        return $this->hasRole(RoleSlug::Empresa->value);
    }

    public function isRepresentante(): bool
    {
        return $this->hasRole(RoleSlug::Representante->value);
    }

    public function isRevendedor(): bool
    {
        return $this->hasRole(RoleSlug::Revendedor->value);
    }

    public function isOperacional(): bool
    {
        return $this->hasRole(RoleSlug::Operacional->value);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('company_id')
            ->withTimestamps();
    }
}
