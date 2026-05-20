<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Enums\RoleSlug;
use App\Models\User;
use App\Modules\Rbac\Models\Role;
use App\Modules\Rbac\Policies\RolePolicy;
use App\Modules\Rbac\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::before(function (?User $user, string $ability)
        {
            if ($user?->is_master)
            {
                return true;
            }

            return null;
        });

        Gate::define('super-admin', fn(User $user) => $user->is_master);

        Gate::define('empresa', fn(User $user) => $user->hasRole(RoleSlug::Empresa->value));

        Gate::define('representante', fn(User $user) => $user->hasRole(RoleSlug::Representante->value));

        Gate::define('revendedor', fn(User $user) => $user->hasRole(RoleSlug::Revendedor->value));

        Gate::define('operacional', fn(User $user) => $user->hasRole(RoleSlug::Operacional->value));

        Gate::define('manage-users', fn(User $user) => $user->hasPermission('users.manage'));

        Gate::define('manage-roles', fn(User $user) => $user->hasPermission('roles.manage'));

        Gate::define('view-audit', fn(User $user) => $user->hasPermission('audit.view'));
    }
}
