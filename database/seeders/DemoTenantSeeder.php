<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Enums\ConsignmentStatus;
use App\Core\Enums\StockMovementType;
use App\Models\User;
use App\Modules\Company\Models\Company;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Consignment\Models\ConsignmentItem;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductCategory;
use App\Modules\Product\Models\StockMovement;
use App\Modules\Representative\Models\Representative;
use App\Modules\Reseller\Models\Reseller;
use Database\Seeders\Concerns\SeedsTenantRbac;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    use SeedsTenantRbac;

    public function run(): void
    {
        $company = Company::query()->updateOrCreate(
            ['document' => '12345678000199'],
            [
                'name' => 'Empresa Demo LTDA',
                'trade_name' => 'Demo Consignados',
                'email' => 'contato@demo.com',
                'is_active' => true,
            ]
        );

        $permissions = $this->seedPermissionsForCompany($company);
        $roles = $this->seedDefaultRoles($company, $permissions);

        $user = User::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'email' => 'admin@demo.com'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('password123'),
                'is_active' => true,
                'is_master' => false,
            ]
        );

        $user->roles()->sync([
            $roles['empresa']->id => ['company_id' => $company->id],
        ]);

        $this->seedDemoCatalog($company);
    }

    private function seedDemoCatalog(Company $company): void
    {
        $category = ProductCategory::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'slug' => 'geral'],
            [
                'name' => 'Geral',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $product = Product::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'sku' => 'DEMO-001'],
            [
                'product_category_id' => $category->id,
                'name' => 'Produto Demonstração',
                'unit_price' => 99.90,
                'cost_price' => 45.00,
                'is_active' => true,
            ]
        );

        StockMovement::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'product_id' => $product->id,
                'movement_type' => StockMovementType::Entrada,
                'reference_type' => Company::class,
                'reference_id' => $company->id,
            ],
            [
                'reseller_id' => null,
                'quantity' => 100,
                'unit_cost' => 45.00,
                'occurred_at' => now(),
                'notes' => 'Estoque inicial demo',
            ]
        );

        $representative = Representative::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'document' => '11111111111'],
            [
                'name' => 'Representante Demo',
                'commission_rate' => 0.05,
                'is_active' => true,
            ]
        );

        $reseller = Reseller::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'document' => '22222222222'],
            [
                'representative_id' => $representative->id,
                'name' => 'Revendedor Demo',
                'is_active' => true,
            ]
        );

        $consignment = Consignment::query()->withoutGlobalScopes()->updateOrCreate(
            ['company_id' => $company->id, 'code' => 'CONS-0001'],
            [
                'reseller_id' => $reseller->id,
                'representative_id' => $representative->id,
                'status' => ConsignmentStatus::Aberto,
                'consigned_at' => now()->toDateString(),
                'expected_return_at' => now()->addDays(30)->toDateString(),
                'dispatched_at' => now(),
            ]
        );

        ConsignmentItem::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'consignment_id' => $consignment->id,
                'product_id' => $product->id,
            ],
            [
                'quantity' => 20,
                'unit_price' => 99.90,
            ]
        );

        StockMovement::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'product_id' => $product->id,
                'movement_type' => StockMovementType::Consignado,
                'reference_type' => Consignment::class,
                'reference_id' => $consignment->id,
            ],
            [
                'reseller_id' => $reseller->id,
                'quantity' => 20,
                'occurred_at' => now(),
                'notes' => 'Consignação demo CONS-0001',
            ]
        );

        StockMovement::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $company->id,
                'product_id' => $product->id,
                'movement_type' => StockMovementType::Saida,
                'reference_type' => Consignment::class,
                'reference_id' => $consignment->id,
            ],
            [
                'reseller_id' => null,
                'quantity' => 20,
                'occurred_at' => now(),
                'notes' => 'Saída do estoque empresa para consignação',
            ]
        );
    }
}
