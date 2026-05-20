<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Sale;

use App\Core\Enums\ConsignmentOperationType;
use App\Core\Enums\CommissionStatus;
use App\Core\Enums\RoleSlug;
use App\Core\Enums\SaleStatus;
use App\Core\Enums\StockMovementType;
use App\Models\User;
use App\Modules\Company\Models\Company;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\StockMovement;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use App\Modules\Representative\Models\Representative;
use App\Modules\Reseller\Models\Reseller;
use App\Modules\Sale\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SaleFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Company $company;

    private Reseller $reseller;

    private Representative $representative;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::query()->create([
            'name' => 'Sale Test Co',
            'document' => '88888888000188',
        ]);

        $permissions = collect([
            'sales.manage',
            'sales.view',
            'consignment.manage',
            'consignment.view',
            'stock.manage',
        ])->map(
            fn(string $slug) => Permission::query()->withoutGlobalScopes()->create([
                'company_id' => $this->company->id,
                'name' => $slug,
                'slug' => $slug,
                'module' => str_contains($slug, 'consignment') ? 'consignment' : 'sale',
            ])
        );

        $role = Role::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'name' => 'Empresa',
            'slug' => RoleSlug::Empresa->value,
            'is_system' => true,
        ]);

        $role->permissions()->sync(
            $permissions->mapWithKeys(fn($p) => [$p->id => ['company_id' => $this->company->id]])->all()
        );

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'sales@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->user->roles()->attach($role->id, ['company_id' => $this->company->id]);

        $this->representative = Representative::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'name' => 'Rep Sales',
            'document' => '55555555555',
            'commission_rate' => 0.10,
            'is_active' => true,
        ]);

        $this->reseller = Reseller::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'representative_id' => $this->representative->id,
            'name' => 'Reseller Sales',
            'document' => '66666666666',
            'is_active' => true,
        ]);

        $this->product = Product::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'sku' => 'SALE-001',
            'name' => 'Produto Venda',
            'unit_price' => 100,
            'cost_price' => 40,
            'is_active' => true,
        ]);

        StockMovement::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'movement_type' => StockMovementType::Entrada,
            'quantity' => 200,
            'occurred_at' => now(),
        ]);
    }

    private function authHeaders(): array
    {
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ])->json('data.token');

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_direct_sale_confirm_with_commission_and_stock(): void
    {
        $headers = $this->authHeaders();

        $create = $this->postJson('/api/v1/sales', [
            'reseller_id' => $this->reseller->id,
            'representative_id' => $this->representative->id,
            'discount' => 50,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
        ], $headers);

        $create->assertCreated()
            ->assertJsonPath('data.status', SaleStatus::Draft->value)
            ->assertJsonPath('data.subtotal', 200)
            ->assertJsonPath('data.total', 150);

        $saleId = $create->json('data.id');

        $this->postJson("/api/v1/sales/{$saleId}/confirm", [], $headers)
            ->assertOk()
            ->assertJsonPath('data.status', SaleStatus::Confirmed->value);

        $this->assertDatabaseHas('sales', [
            'id' => $saleId,
            'status' => SaleStatus::Confirmed->value,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'reference_id' => $saleId,
            'movement_type' => StockMovementType::Saida->value,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('commissions', [
            'sale_id' => $saleId,
            'amount' => 15,
            'status' => CommissionStatus::Pending->value,
        ]);
    }

    public function test_consignment_linked_sale_confirm(): void
    {
        $headers = $this->authHeaders();

        $consignment = $this->postJson('/api/v1/consignments', [
            'reseller_id' => $this->reseller->id,
            'representative_id' => $this->representative->id,
            'consigned_at' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 20, 'unit_price' => 100],
            ],
        ], $headers)->assertCreated();

        $consignmentId = $consignment->json('data.id');
        $itemId = $consignment->json('data.items.0.id');

        $this->postJson("/api/v1/consignments/{$consignmentId}/dispatch", [], $headers)->assertOk();

        $sale = $this->postJson('/api/v1/sales', [
            'reseller_id' => $this->reseller->id,
            'representative_id' => $this->representative->id,
            'consignment_id' => $consignmentId,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'consignment_item_id' => $itemId,
                ],
            ],
        ], $headers)->assertCreated();

        $saleId = $sale->json('data.id');

        $this->postJson("/api/v1/sales/{$saleId}/confirm", [], $headers)->assertOk();

        $this->assertDatabaseHas('consignment_operations', [
            'consignment_id' => $consignmentId,
            'operation_type' => ConsignmentOperationType::VendaParcial->value,
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'reference_id' => $saleId,
            'movement_type' => StockMovementType::Venda->value,
            'reseller_id' => $this->reseller->id,
        ]);

        $consignmentModel = Consignment::query()->find($consignmentId);
        $this->assertSame(5, $consignmentModel->items()->first()->quantity_sold);
    }

    public function test_cannot_confirm_without_stock(): void
    {
        $headers = $this->authHeaders();

        $product = Product::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'sku' => 'NO-STOCK-SALE',
            'name' => 'Sem estoque venda',
            'unit_price' => 10,
            'is_active' => true,
        ]);

        $create = $this->postJson('/api/v1/sales', [
            'reseller_id' => $this->reseller->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ], $headers)->assertCreated();

        $this->postJson(
            '/api/v1/sales/' . $create->json('data.id') . '/confirm',
            [],
            $headers
        )->assertUnprocessable();
    }

    public function test_cancel_draft_sale_and_block_cancel_when_confirmed(): void
    {
        $headers = $this->authHeaders();

        $create = $this->postJson('/api/v1/sales', [
            'reseller_id' => $this->reseller->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
        ], $headers)->assertCreated();

        $saleId = $create->json('data.id');

        $this->postJson("/api/v1/sales/{$saleId}/cancel", [], $headers)
            ->assertOk()
            ->assertJsonPath('data.status', SaleStatus::Cancelled->value);

        $confirmed = $this->postJson('/api/v1/sales', [
            'reseller_id' => $this->reseller->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
        ], $headers)->assertCreated();

        $confirmedId = $confirmed->json('data.id');
        $this->postJson("/api/v1/sales/{$confirmedId}/confirm", [], $headers)->assertOk();

        $this->postJson("/api/v1/sales/{$confirmedId}/cancel", [], $headers)
            ->assertForbidden();
    }

    public function test_dashboard_and_report_endpoints(): void
    {
        $headers = $this->authHeaders();

        Sale::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'reseller_id' => $this->reseller->id,
            'representative_id' => $this->representative->id,
            'code' => 'VND-TEST',
            'subtotal' => 100,
            'discount' => 0,
            'total' => 100,
            'status' => SaleStatus::Confirmed,
            'sold_at' => now(),
        ]);

        $this->getJson('/api/v1/sales/dashboard', $headers)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['period', 'summary', 'top_products', 'by_reseller']]);

        $this->getJson('/api/v1/sales/report?date_from=' . now()->startOfMonth()->toDateString(), $headers)
            ->assertOk()
            ->assertJsonStructure(['data' => ['filters', 'by_reseller', 'top_products', 'totals']]);
    }

    public function test_list_sales_with_filters(): void
    {
        $headers = $this->authHeaders();

        $this->postJson('/api/v1/sales', [
            'reseller_id' => $this->reseller->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
        ], $headers)->assertCreated();

        $this->getJson('/api/v1/sales?status=draft&reseller_id=' . $this->reseller->id, $headers)
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }
}
