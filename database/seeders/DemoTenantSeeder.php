<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Company\Models\Company;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->create([
            'name' => 'Empresa Demo LTDA',
            'trade_name' => 'Demo Consignados',
            'document' => '12345678000199',
            'email' => 'contato@demo.com',
            'is_active' => true,
        ]);

        $role = Role::query()->create([
            'company_id' => $company->id,
            'name' => 'Administrador',
            'slug' => 'admin',
            'description' => 'Acesso total ao tenant',
        ]);

        $role->permissions()->sync(
            Permission::query()->pluck('id')
        );

        $user = User::query()->create([
            'company_id' => $company->id,
            'name' => 'Admin Demo',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id);
    }
}
