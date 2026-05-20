<?php

declare(strict_types=1);

namespace App\Modules\Sale\Services;

use App\Core\Enums\ConsignmentOperationType;
use App\Core\Enums\ConsignmentStatus;
use App\Core\Enums\SaleStatus;
use App\Core\Enums\StockMovementType;
use App\Core\Services\BaseService;
use App\Core\Tenant\TenantContext;
use App\Modules\Commission\Services\CommissionService;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Consignment\Models\ConsignmentItem;
use App\Modules\Consignment\Models\ConsignmentOperation;
use App\Modules\Consignment\Repositories\ConsignmentItemRepository;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Services\StockMovementService;
use App\Modules\Sale\DTOs\SaleDTO;
use App\Modules\Sale\DTOs\SaleFilterDTO;
use App\Modules\Sale\Events\SaleCancelled;
use App\Modules\Sale\Events\SaleConfirmed;
use App\Modules\Sale\Events\SaleCreated;
use App\Modules\Sale\Models\Sale;
use App\Modules\Sale\Models\SaleItem;
use App\Modules\Sale\Repositories\SaleItemRepository;
use App\Modules\Sale\Repositories\SaleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService extends BaseService
{
    public function __construct(
        SaleRepository $repository,
        private SaleItemRepository $items,
        private ConsignmentItemRepository $consignmentItems,
        private StockMovementService $stock,
        private CommissionService $commissions,
    )
    {
        parent::__construct($repository);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginateWithFilters($perPage, $filters);
    }

    public function findDetails(string $id): Sale
    {
        return $this->repository->findWithDetails($id);
    }

    public function store(SaleDTO $dto): Sale
    {
        return DB::transaction(function () use ($dto)
        {
            $companyId = (string) TenantContext::companyId();
            $this->validateConsignmentContext($dto);

            $totals = $this->calculateTotals($dto->items, $dto->discount);

            $sale = $this->repository->create([
                'company_id' => $companyId,
                'reseller_id' => $dto->reseller_id,
                'customer_id' => $dto->customer_id,
                'representative_id' => $dto->representative_id,
                'consignment_id' => $dto->consignment_id,
                'code' => $this->repository->nextCode($companyId),
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'total' => $totals['total'],
                'status' => SaleStatus::Draft,
            ]);

            $this->syncItems($sale, $dto->items);

            event(new SaleCreated($sale->fresh()->load('items.product')));

            return $this->repository->findWithDetails($sale->id);
        });
    }

    public function updateFromDto(string $id, SaleDTO $dto): Sale
    {
        return DB::transaction(function () use ($id, $dto)
        {
            $sale = $this->repository->findWithDetails($id);
            $this->assertEditable($sale);
            $this->validateConsignmentContext($dto, $sale);

            $totals = $this->calculateTotals($dto->items, $dto->discount);

            $sale->update([
                'reseller_id' => $dto->reseller_id,
                'customer_id' => $dto->customer_id,
                'representative_id' => $dto->representative_id,
                'consignment_id' => $dto->consignment_id,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'total' => $totals['total'],
            ]);

            $sale->items()->delete();
            $this->syncItems($sale, $dto->items);

            return $this->repository->findWithDetails($sale->id);
        });
    }

    public function confirm(string $id): Sale
    {
        return DB::transaction(function () use ($id)
        {
            $sale = $this->repository->findWithDetails($id);
            $this->assertEditable($sale);

            if ($sale->items->isEmpty())
            {
                throw ValidationException::withMessages([
                    'items' => ['A venda deve possuir ao menos um item.'],
                ]);
            }

            foreach ($sale->items as $item)
            {
                $this->processStockForItem($sale, $item);
            }

            $sale->update([
                'status' => SaleStatus::Confirmed,
                'sold_at' => now(),
            ]);

            $this->commissions->createFromSale($sale->fresh());
            $this->syncConsignmentStatus($sale->consignment_id);

            event(new SaleConfirmed($sale->fresh()->load(['items.product', 'commission'])));

            return $this->repository->findWithDetails($sale->id);
        });
    }

    public function cancel(string $id): Sale
    {
        return DB::transaction(function () use ($id)
        {
            $sale = $this->repository->findWithDetails($id);
            $this->assertEditable($sale);

            $sale->update(['status' => SaleStatus::Cancelled]);
            $this->commissions->cancelForSale($sale);

            event(new SaleCancelled($sale->fresh()));

            return $this->repository->findWithDetails($sale->id);
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id)
        {
            $sale = $this->repository->findWithDetails($id);
            $this->assertEditable($sale);

            if ($sale->status !== SaleStatus::Draft)
            {
                throw ValidationException::withMessages([
                    'sale' => ['Somente vendas em rascunho podem ser excluídas.'],
                ]);
            }

            return $this->repository->delete($id);
        });
    }

    public function dashboard(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $companyId = (string) TenantContext::companyId();
        $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->startOfMonth();
        $to = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        $stats = $this->repository->dashboardStats($companyId, $from, $to);
        $filters = ['date_from' => $from->toDateString(), 'date_to' => $to->toDateString()];

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'summary' => $stats,
            'top_products' => $this->repository->reportTopProducts($companyId, $filters, 5),
            'by_reseller' => $this->repository->reportByReseller($companyId, $filters),
        ];
    }

    public function report(SaleFilterDTO $filters): array
    {
        $companyId = (string) TenantContext::companyId();
        $filterArray = array_merge($filters->toArray(), ['confirmed_only' => true]);

        return [
            'filters' => $filterArray,
            'by_reseller' => $this->repository->reportByReseller($companyId, $filterArray),
            'top_products' => $this->repository->reportTopProducts($companyId, $filterArray, 20),
            'totals' => $this->repository->dashboardStats(
                $companyId,
                isset($filterArray['date_from']) ? Carbon::parse($filterArray['date_from']) : null,
                isset($filterArray['date_to']) ? Carbon::parse($filterArray['date_to']) : null,
            ),
        ];
    }

    /**
     * @param  list<array{product_id: string, quantity: int, unit_price?: float|null, consignment_item_id?: string|null}>  $items
     * @return array{subtotal: float, discount: float, total: float}
     */
    public function calculateTotals(array $items, float $discount): array
    {
        $subtotal = 0.0;

        foreach ($items as $row)
        {
            $product = Product::query()->findOrFail($row['product_id']);
            $unitPrice = isset($row['unit_price']) ? (float) $row['unit_price'] : (float) $product->unit_price;
            $subtotal += (int) $row['quantity'] * $unitPrice;
        }

        $discount = max(0, round($discount, 2));
        $total = max(0, round($subtotal - $discount, 2));

        if ($discount > $subtotal)
        {
            throw ValidationException::withMessages([
                'discount' => ['Desconto não pode ser maior que o subtotal.'],
            ]);
        }

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'total' => $total,
        ];
    }

    private function syncItems(Sale $sale, array $items): void
    {
        foreach ($items as $row)
        {
            $product = Product::query()->findOrFail($row['product_id']);
            $unitPrice = isset($row['unit_price']) ? (float) $row['unit_price'] : (float) $product->unit_price;
            $qty = (int) $row['quantity'];
            $lineTotal = round($qty * $unitPrice, 2);

            $this->items->create([
                'company_id' => $sale->company_id,
                'sale_id' => $sale->id,
                'product_id' => $row['product_id'],
                'consignment_item_id' => $row['consignment_item_id'] ?? null,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total' => $lineTotal,
            ]);
        }
    }

    private function processStockForItem(Sale $sale, SaleItem $item): void
    {
        if ($item->consignment_item_id !== null)
        {
            $consignmentItem = $this->consignmentItems->findForConsignment(
                (string) $sale->consignment_id,
                $item->consignment_item_id
            );

            if ($item->quantity > $consignmentItem->quantityPending())
            {
                throw ValidationException::withMessages([
                    'items' => ["Quantidade excede consignado pendente do produto {$item->product_id}."],
                ]);
            }

            $operation = ConsignmentOperation::query()->create([
                'company_id' => $sale->company_id,
                'consignment_id' => $sale->consignment_id,
                'consignment_item_id' => $consignmentItem->id,
                'product_id' => $item->product_id,
                'operation_type' => ConsignmentOperationType::VendaParcial,
                'quantity' => $item->quantity,
                'notes' => "Venda {$sale->code}",
                'metadata' => ['sale_id' => $sale->id, 'sale_item_id' => $item->id],
                'performed_by' => Auth::id(),
            ]);

            $this->stock->record(
                companyId: $sale->company_id,
                productId: $item->product_id,
                type: StockMovementType::Venda,
                quantity: $item->quantity,
                resellerId: $sale->reseller_id,
                reference: $sale,
                operation: $operation,
                notes: "Venda confirmada {$sale->code}",
            );

            $consignmentItem->increment('quantity_sold', $item->quantity);

            return;
        }

        if ($sale->consignment_id !== null)
        {
            throw ValidationException::withMessages([
                'items' => ['Itens de venda consignada devem referenciar consignment_item_id.'],
            ]);
        }

        $this->stock->assertSufficientCompanyStock(
            Product::query()->findOrFail($item->product_id),
            $item->quantity
        );

        $this->stock->record(
            companyId: $sale->company_id,
            productId: $item->product_id,
            type: StockMovementType::Saida,
            quantity: $item->quantity,
            resellerId: null,
            reference: $sale,
            notes: "Venda direta {$sale->code}",
        );
    }

    private function validateConsignmentContext(SaleDTO $dto, ?Sale $existing = null): void
    {
        if ($dto->consignment_id === null)
        {
            foreach ($dto->items as $row)
            {
                if (! empty($row['consignment_item_id']))
                {
                    throw ValidationException::withMessages([
                        'consignment_id' => ['Informe o consignado quando houver itens consignados.'],
                    ]);
                }
            }

            return;
        }

        $consignment = Consignment::query()->findOrFail($dto->consignment_id);

        if (! $consignment->isDispatched())
        {
            throw ValidationException::withMessages([
                'consignment_id' => ['Consignado ainda não foi enviado.'],
            ]);
        }

        if ($consignment->reseller_id !== $dto->reseller_id)
        {
            throw ValidationException::withMessages([
                'reseller_id' => ['Revendedor deve ser o mesmo do consignado.'],
            ]);
        }

        if ($consignment->isClosed())
        {
            throw ValidationException::withMessages([
                'consignment_id' => ['Consignado já está fechado.'],
            ]);
        }
    }

    private function syncConsignmentStatus(?string $consignmentId): void
    {
        if ($consignmentId === null)
        {
            return;
        }

        $consignment = Consignment::query()->with('items')->find($consignmentId);

        if ($consignment === null || $consignment->isClosed())
        {
            return;
        }

        $hasPending = $consignment->items->contains(fn(ConsignmentItem $i) => $i->quantityPending() > 0);

        $consignment->update([
            'status' => $hasPending ? ConsignmentStatus::Parcial : ConsignmentStatus::Aberto,
        ]);
    }

    private function assertEditable(Sale $sale): void
    {
        if ($sale->status === SaleStatus::Confirmed)
        {
            throw ValidationException::withMessages([
                'sale' => ['Venda confirmada não pode ser alterada.'],
            ]);
        }

        if ($sale->status === SaleStatus::Cancelled)
        {
            throw ValidationException::withMessages([
                'sale' => ['Venda cancelada não pode ser alterada.'],
            ]);
        }
    }
}
