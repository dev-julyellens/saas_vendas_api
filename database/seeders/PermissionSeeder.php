<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsTenantRbac;
use Illuminate\Database\Seeder;

/**
 * Permissões são por tenant — use TenantBootstrapSeeder ou DemoTenantSeeder.
 * Mantido vazio para não duplicar registros sem company_id.
 */
class PermissionSeeder extends Seeder
{
    use SeedsTenantRbac;

    public function run(): void
    {
        // Definições em SeedsTenantRbac::permissionDefinitions().
        // Aplicadas por DemoTenantSeeder / MasterAdminSeeder na criação da empresa.
    }
}
