<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use App\Modules\Company\Models\Company;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use Illuminate\Support\Collection;

trait SeedsTenantRbac
{
    /** @return list<array{name: string, slug: string, module: string, description?: string}> */
    protected function permissionDefinitions(): array
    {
        return [
            ['name' => 'Gerenciar produtos', 'slug' => 'products.manage', 'module' => 'product'],
            ['name' => 'Visualizar produtos', 'slug' => 'products.view', 'module' => 'product'],
            ['name' => 'Gerenciar categorias', 'slug' => 'categories.manage', 'module' => 'product'],
            ['name' => 'Gerenciar estoque', 'slug' => 'stock.manage', 'module' => 'product'],
            ['name' => 'Gerenciar consignação', 'slug' => 'consignment.manage', 'module' => 'consignment'],
            ['name' => 'Visualizar consignação', 'slug' => 'consignment.view', 'module' => 'consignment'],
            ['name' => 'Gerenciar vendas', 'slug' => 'sales.manage', 'module' => 'sale'],
            ['name' => 'Visualizar vendas', 'slug' => 'sales.view', 'module' => 'sale'],
            ['name' => 'Gerenciar devoluções', 'slug' => 'returns.manage', 'module' => 'return'],
            ['name' => 'Gerenciar comissões', 'slug' => 'commissions.manage', 'module' => 'commission'],
            ['name' => 'Gerenciar financeiro', 'slug' => 'financial.manage', 'module' => 'financial'],
            ['name' => 'Gerenciar representantes', 'slug' => 'representatives.manage', 'module' => 'representative'],
            ['name' => 'Gerenciar revendedores', 'slug' => 'resellers.manage', 'module' => 'reseller'],
            ['name' => 'Gerenciar clientes', 'slug' => 'customers.manage', 'module' => 'customer'],
            ['name' => 'Gerenciar usuários', 'slug' => 'users.manage', 'module' => 'rbac'],
            ['name' => 'Gerenciar papéis', 'slug' => 'roles.manage', 'module' => 'rbac'],
            ['name' => 'Visualizar auditoria', 'slug' => 'audit.view', 'module' => 'audit'],
        ];
    }

    protected function seedPermissionsForCompany(Company $company): Collection
    {
        return collect($this->permissionDefinitions())->map(
            fn(array $definition) => Permission::query()->withoutGlobalScopes()->updateOrCreate(
                ['company_id' => $company->id, 'slug' => $definition['slug']],
                array_merge($definition, ['company_id' => $company->id])
            )
        );
    }

    /**
     * @return array{admin: Role, manager: Role, representative: Role, reseller: Role}
     */
    protected function seedDefaultRoles(Company $company, Collection $permissions): array
    {
        $pivot = fn(array $slugs) => $permissions
            ->whereIn('slug', $slugs)
            ->mapWithKeys(fn(Permission $p) => [$p->id => ['company_id' => $company->id]])
            ->all();

        $admin = Role::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'slug' => 'admin'],
            [
                'name' => 'Administrador',
                'description' => 'Acesso total ao tenant',
                'is_system' => true,
            ]
        );
        $admin->permissions()->sync(
            $permissions->mapWithKeys(fn(Permission $p) => [$p->id => ['company_id' => $company->id]])->all()
        );

        $manager = Role::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'slug' => 'manager'],
            [
                'name' => 'Gerente',
                'description' => 'Operação comercial e financeira',
                'is_system' => true,
            ]
        );
        $manager->permissions()->sync($pivot([
            'products.manage',
            'products.view',
            'categories.manage',
            'stock.manage',
            'consignment.manage',
            'consignment.view',
            'sales.manage',
            'sales.view',
            'returns.manage',
            'commissions.manage',
            'financial.manage',
            'representatives.manage',
            'resellers.manage',
            'customers.manage',
        ]));

        $representative = Role::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'slug' => 'representative'],
            [
                'name' => 'Representante',
                'description' => 'Consignação e vendas da carteira',
                'is_system' => true,
            ]
        );
        $representative->permissions()->sync($pivot([
            'products.view',
            'consignment.manage',
            'consignment.view',
            'sales.manage',
            'sales.view',
            'returns.manage',
            'customers.manage',
        ]));

        $reseller = Role::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'slug' => 'reseller'],
            [
                'name' => 'Revendedor',
                'description' => 'Consulta de consignação e registro de vendas',
                'is_system' => true,
            ]
        );
        $reseller->permissions()->sync($pivot([
            'products.view',
            'consignment.view',
            'sales.manage',
            'sales.view',
        ]));

        return compact('admin', 'manager', 'representative', 'reseller');
    }
}
