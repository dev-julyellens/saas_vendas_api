<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Admin master da plataforma — is_master=true, sem tenant (company_id null).
 * Bypass de CompanyScope e permissões via User::hasPermission().
 */
class MasterAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->withoutGlobalScopes()->updateOrCreate(
            ['email' => 'master@saas.local'],
            [
                'company_id' => null,
                'name' => 'Master Admin',
                'password' => Hash::make('Master@123'),
                'is_active' => true,
                'is_master' => true,
            ]
        );
    }
}
