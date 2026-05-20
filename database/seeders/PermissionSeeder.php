<?php

namespace Database\Seeders;

use App\Modules\Rbac\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Gerenciar produtos', 'slug' => 'products.manage', 'module' => 'product'],
            ['name' => 'Gerenciar vendas', 'slug' => 'sales.manage', 'module' => 'sale'],
            ['name' => 'Gerenciar consignação', 'slug' => 'consignment.manage', 'module' => 'consignment'],
            ['name' => 'Gerenciar financeiro', 'slug' => 'financial.manage', 'module' => 'financial'],
            ['name' => 'Gerenciar usuários', 'slug' => 'users.manage', 'module' => 'rbac'],
        ];

        foreach ($permissions as $permission)
        {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
