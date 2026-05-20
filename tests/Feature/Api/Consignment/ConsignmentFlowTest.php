<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Consignment;

use App\Core\Enums\ConsignmentOperationType;
use App\Core\Enums\ConsignmentStatus;
use App\Core\Enums\RoleSlug;
use App\Core\Enums\StockMovementType;
use App\Models\User;
use App\Modules\Company\Models\Company;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Consignment\Models\ConsignmentOperation;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\StockMovement;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use App\Modules\Representative\Models\Representative;
use App\Modules\Reseller\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConsignmentFlowTest extends TestCase
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
            'name' => 'Consignment Test Co',
            'document' => '99999999000199',
        ]);

        $permissions = collect(['consignment.manage', 'consignment.view', 'stock.manage'])->map(
            fn(string $slug) => Permission::query()->withoutGlobalScopes()->create([
                'company_id' => $this->company->id,
                'name' => $slug,
                'slug' => $slug,
                'module' => 'consignment',
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
            'email' => 'consignment@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->user->roles()->attach($role->id, ['company_id' => $this->company->id]);

        $this->representative = Representative::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'name' => 'Rep Test',
            'document' => '33333333333',
            'is_active' => true,
        ]);

        $this->reseller = Reseller::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'representative_id' => $this->representative->id,
            'name' => 'Reseller Test',
            'document' => '44444444444',
            'is_active' => true,
        ]);

        $this->product = Product::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'sku' => 'FLOW-001',
            'name' => 'Produto Flow',
            'unit_price' => 50,
            'cost_price' => 20,
            'is_active' => true,
        ]);

        StockMovement::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'movement_type' => StockMovementType::Entrada,
            'quantity' => 100,
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

    public function test_full_consignment_flow(): void
    {
        $headers = $this->authHeaders();

        $create = $this->postJson('/api/v1/consignments', [
            'reseller_id' => $this->reseller->id,
            'representative_id' => $this->representative->id,
            'consigned_at' => now()->toDateString(),
            'expected_return_at' => now()->addDays(15)->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 30, 'unit_price' => 50],
            ],
        ], $headers);

        $create->assertCreated();
        $consignmentId = $create->json('data.id');
        $itemId = $create->json('data.items.0.id');

        $this->postJson("/api/v1/consignments/{$consignmentId}/dispatch", [], $headers)
            ->assertOk();

        $this->assertDatabaseHas('stock_movements', [
            'reference_id' => $consignmentId,
            'movement_type' => StockMovementType::Saida->value,
        ]);

        $this->postJson("/api/v1/consignments/{$consignmentId}/partial-sale", [
            'consignment_item_id' => $itemId,
            'quantity' => 10,
        ], $headers)->assertOk();

        $this->assertDatabaseHas('consignment_operations', [
            'consignment_id' => $consignmentId,
            'operation_type' => ConsignmentOperationType::VendaParcial->value,
            'quantity' => 10,
        ]);

        $this->postJson("/api/v1/consignments/{$consignmentId}/collect", [
            'notes' => 'Coleta representante',
        ], $headers)->assertOk();

        $this->postJson("/api/v1/consignments/{$consignmentId}/close", [], $headers)
            ->assertOk()
            ->assertJsonPath('data.status', ConsignmentStatus::Fechado->value);

        $consignment = Consignment::query()->find($consignmentId);
        $this->assertSame(ConsignmentStatus::Fechado, $consignment->status);
        $this->assertNotNull($consignment->closed_at);

        $item = $consignment->items()->first();
        $this->assertSame(30, $item->quantity_sold + $item->quantity_returned);
    }

    public function test_cannot_dispatch_without_stock(): void
    {
        $headers = $this->authHeaders();

        $product = Product::query()->withoutGlobalScopes()->create([
            'company_id' => $this->company->id,
            'sku' => 'NO-STOCK',
            'name' => 'Sem estoque',
            'unit_price' => 10,
            'is_active' => true,
        ]);

        $create = $this->postJson('/api/v1/consignments', [
            'reseller_id' => $this->reseller->id,
            'consigned_at' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ], $headers)->assertCreated();

        $this->postJson(
            '/api/v1/consignments/' . $create->json('data.id') . '/dispatch',
            [],
            $headers
        )->assertUnprocessable();
    }

    public function test_operations_and_movements_endpoints(): void
    {
        $headers = $this->authHeaders();

        $create = $this->postJson('/api/v1/consignments', [
            'reseller_id' => $this->reseller->id,
            'consigned_at' => now()->toDateString(),
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5],
            ],
        ], $headers)->assertCreated();

        $id = $create->json('data.id');
        $this->postJson("/api/v1/consignments/{$id}/dispatch", [], $headers)->assertOk();

        $this->getJson("/api/v1/consignments/{$id}/operations", $headers)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson("/api/v1/consignments/{$id}/movements", $headers)
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
