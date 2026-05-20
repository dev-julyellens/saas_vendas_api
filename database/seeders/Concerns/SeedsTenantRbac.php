<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use App\Core\Enums\RoleSlug;
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
     * Perfis: Empresa, Operacional, Representante, Revendedor.
     *
     * @return array{empresa: Role, operacional: Role, representante: Role, revendedor: Role}
     */
    protected function seedDefaultRoles(Company $company, Collection $permissions): array
    {
        $pivot = fn(array $slugs) => $permissions
            ->whereIn('slug', $slugs)
            ->mapWithKeys(fn(Permission $p) => [$p->id => ['company_id' => $company->id]])
            ->all();

        $empresa = Role::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'slug' => RoleSlug::Empresa->value],
            [
                'name' => 'Empresa',
                'description' => 'Administrador do tenant — acesso total',
                'is_system' => true,
            ]
        );
        $empresa->permissions()->sync(
            $permissions->mapWithKeys(fn(Permission $p) => [$p->id => ['company_id' => $company->id]])->all()
        );

        $operacional = Role::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'slug' => RoleSlug::Operacional->value],
            [
                'name' => 'Operacional',
                'description' => 'Operações do dia a dia (estoque, vendas, consignação)',
                'is_system' => true,
            ]
        );
        $operacional->permissions()->sync($pivot([
            'products.manage',
            'products.view',
            'categories.manage',
            'stock.manage',
            'consignment.manage',
            'consignment.view',
            'sales.manage',
            'sales.view',
            'returns.manage',
            'customers.manage',
            'resellers.manage',
        ]));

        $representante = Role::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'slug' => RoleSlug::Representante->value],
            [
                'name' => 'Representante',
                'description' => 'Carteira de revendedores e consignação',
                'is_system' => true,
            ]
        );
        $representante->permissions()->sync($pivot([
            'products.view',
            'consignment.manage',
            'consignment.view',
            'sales.manage',
            'sales.view',
            'returns.manage',
            'customers.manage',
        ]));

        $revendedor = Role::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'slug' => RoleSlug::Revendedor->value],
            [
                'name' => 'Revendedor',
                'description' => 'Vendas e consulta de consignação',
                'is_system' => true,
            ]
        );
        $revendedor->permissions()->sync($pivot([
            'products.view',
            'consignment.view',
            'sales.manage',
            'sales.view',
        ]));

        return compact('empresa', 'operacional', 'representante', 'revendedor');
    }
}
