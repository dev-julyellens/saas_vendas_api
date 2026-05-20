<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Services;

use App\Core\Enums\ConsignmentOperationType;
use App\Core\Enums\ConsignmentStatus;
use App\Core\Enums\StockMovementType;
use App\Core\Services\BaseService;
use App\Core\Tenant\TenantContext;
use App\Modules\Consignment\DTOs\ConsignmentDTO;
use App\Modules\Consignment\DTOs\ConsignmentItemActionDTO;
use App\Modules\Consignment\Events\ConsignmentClosed;
use App\Modules\Consignment\Events\ConsignmentDispatched;
use App\Modules\Consignment\Events\ConsignmentOperationRecorded;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Consignment\Models\ConsignmentItem;
use App\Modules\Consignment\Models\ConsignmentOperation;
use App\Modules\Consignment\Repositories\ConsignmentItemRepository;
use App\Modules\Consignment\Repositories\ConsignmentOperationRepository;
use App\Modules\Consignment\Repositories\ConsignmentRepository;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\StockMovementRepository;
use App\Modules\Product\Services\StockMovementService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsignmentService extends BaseService
{
    public function __construct(
        ConsignmentRepository $repository,
        private ConsignmentItemRepository $items,
        private ConsignmentOperationRepository $operations,
        private StockMovementService $stock,
        private StockMovementRepository $stockMovements,
    )
    {
        parent::__construct($repository);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginateWithRelations($perPage, $filters);
    }

    public function findDetails(string $id): Consignment
    {
        $consignment = $this->repository->findWithDetails($id);
        $this->syncOverdueStatus($consignment);

        return $consignment->fresh([
            'items.product',
            'reseller',
            'representative',
            'operations.performedBy',
            'operations.product',
        ]);
    }

    public function store(ConsignmentDTO $dto): Consignment
    {
        return DB::transaction(function () use ($dto)
        {
            $companyId = (string) TenantContext::companyId();

            $consignment = $this->repository->create([
                'company_id' => $companyId,
                'reseller_id' => $dto->reseller_id,
                'representative_id' => $dto->representative_id,
                'code' => $this->repository->nextCode($companyId),
                'status' => ConsignmentStatus::Aberto,
                'consigned_at' => $dto->consigned_at,
                'expected_return_at' => $dto->expected_return_at,
                'notes' => $dto->notes,
            ]);

            foreach ($dto->items as $row)
            {
                $this->items->create([
                    'company_id' => $companyId,
                    'consignment_id' => $consignment->id,
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'] ?? null,
                ]);
            }

            return $consignment->load('items.product');
        });
    }

    /** Envio: empresa → revendedor (stock_movements). */
    public function dispatch(string $consignmentId): Consignment
    {
        return DB::transaction(function () use ($consignmentId)
        {
            $consignment = $this->repository->findWithDetails($consignmentId);
            $this->assertCanModify($consignment);
            $this->assertNotDispatched($consignment);

            foreach ($consignment->items as $item)
            {
                $product = $item->product ?? Product::query()->findOrFail($item->product_id);
                $this->stock->assertSufficientCompanyStock($product, (int) $item->quantity);

                $operation = $this->recordOperation(
                    $consignment,
                    $item,
                    ConsignmentOperationType::Envio,
                    (int) $item->quantity,
                    'Envio de mercadoria consignada'
                );

                $this->stock->record(
                    companyId: $consignment->company_id,
                    productId: $item->product_id,
                    type: StockMovementType::Saida,
                    quantity: (int) $item->quantity,
                    resellerId: null,
                    reference: $consignment,
                    operation: $operation,
                    notes: "Saída empresa — consignado {$consignment->code}",
                    unitCost: $product->cost_price ? (float) $product->cost_price : null,
                );

                $this->stock->record(
                    companyId: $consignment->company_id,
                    productId: $item->product_id,
                    type: StockMovementType::Consignado,
                    quantity: (int) $item->quantity,
                    resellerId: $consignment->reseller_id,
                    reference: $consignment,
                    operation: $operation,
                    notes: "Consignado no revendedor — {$consignment->code}",
                );
            }

            $consignment->update([
                'dispatched_at' => now(),
                'status' => ConsignmentStatus::Aberto,
            ]);

            $this->syncOverdueStatus($consignment->fresh());
            event(new ConsignmentDispatched($consignment->fresh()->load('items')));

            return $this->findDetails($consignment->id);
        });
    }

    public function partialSale(string $consignmentId, ConsignmentItemActionDTO $dto): Consignment
    {
        return $this->applyItemOperation(
            $consignmentId,
            $dto,
            ConsignmentOperationType::VendaParcial,
            StockMovementType::Venda,
            'quantity_sold',
            'Venda parcial consignada'
        );
    }

    public function partialReturn(string $consignmentId, ConsignmentItemActionDTO $dto): Consignment
    {
        return DB::transaction(function () use ($consignmentId, $dto)
        {
            $consignment = $this->loadForOperation($consignmentId);
            $item = $this->items->findForConsignment($consignmentId, $dto->consignment_item_id);
            $this->assertQuantityAvailable($item, $dto->quantity);

            $operation = $this->recordOperation(
                $consignment,
                $item,
                ConsignmentOperationType::DevolucaoParcial,
                $dto->quantity,
                $dto->notes ?? 'Devolução parcial ao estoque da empresa'
            );

            $this->stock->assertSufficientResellerStock(
                $consignment->company_id,
                $item->product_id,
                $consignment->reseller_id,
                $dto->quantity
            );

            $this->stock->record(
                companyId: $consignment->company_id,
                productId: $item->product_id,
                type: StockMovementType::Devolucao,
                quantity: $dto->quantity,
                resellerId: $consignment->reseller_id,
                reference: $consignment,
                operation: $operation,
                notes: $dto->notes,
            );

            $this->stock->record(
                companyId: $consignment->company_id,
                productId: $item->product_id,
                type: StockMovementType::Devolucao,
                quantity: $dto->quantity,
                resellerId: null,
                reference: $consignment,
                operation: $operation,
                notes: 'Entrada na empresa — devolução consignado',
            );

            $item->increment('quantity_returned', $dto->quantity);
            $this->afterItemMutation($consignment, $item, $operation);

            return $this->findDetails($consignment->id);
        });
    }

    public function registerLoss(string $consignmentId, ConsignmentItemActionDTO $dto): Consignment
    {
        return $this->applyItemOperation(
            $consignmentId,
            $dto,
            ConsignmentOperationType::Perda,
            StockMovementType::Perda,
            'quantity_lost',
            $dto->notes ?? 'Perda de mercadoria consignada'
        );
    }

    public function registerDamage(string $consignmentId, ConsignmentItemActionDTO $dto): Consignment
    {
        return $this->applyItemOperation(
            $consignmentId,
            $dto,
            ConsignmentOperationType::Avaria,
            StockMovementType::Avaria,
            'quantity_damaged',
            $dto->notes ?? 'Avaria em mercadoria consignada'
        );
    }

    public function registerDivergence(string $consignmentId, ConsignmentItemActionDTO $dto): Consignment
    {
        return DB::transaction(function () use ($consignmentId, $dto)
        {
            $consignment = $this->loadForOperation($consignmentId);
            $item = $this->items->findForConsignment($consignmentId, $dto->consignment_item_id);
            $this->assertQuantityAvailable($item, $dto->quantity);

            $operation = $this->recordOperation(
                $consignment,
                $item,
                ConsignmentOperationType::Divergencia,
                $dto->quantity,
                $dto->notes ?? 'Divergência na conferência'
            );

            $item->increment('quantity_divergence', $dto->quantity);
            $this->afterItemMutation($consignment, $item, $operation);

            return $this->findDetails($consignment->id);
        });
    }

    /** Coleta pelo representante — registra evento sem movimentar estoque. */
    public function collect(string $consignmentId, ?string $notes = null): Consignment
    {
        return DB::transaction(function () use ($consignmentId, $notes)
        {
            $consignment = $this->loadForOperation($consignmentId);

            if ($consignment->representative_id === null)
            {
                throw ValidationException::withMessages([
                    'representative_id' => ['Consignado sem representante vinculado.'],
                ]);
            }

            $operation = $this->recordOperation(
                $consignment,
                null,
                ConsignmentOperationType::Coleta,
                0,
                $notes ?? 'Coleta realizada pelo representante',
                metadata: [
                    'items_count' => $consignment->items->count(),
                    'pending_total' => $consignment->items->sum(fn($i) => $i->quantityPending()),
                ]
            );

            $consignment->update(['collected_at' => now()]);
            event(new ConsignmentOperationRecorded($consignment->fresh(), $operation));

            return $this->findDetails($consignment->id);
        });
    }

    /** Fechamento: devolve saldo pendente e encerra. */
    public function close(string $consignmentId, ?string $notes = null): Consignment
    {
        return DB::transaction(function () use ($consignmentId, $notes)
        {
            $consignment = $this->loadForOperation($consignmentId);

            foreach ($consignment->items as $item)
            {
                $pending = $item->quantityPending();

                if ($pending > 0)
                {
                    $dto = new ConsignmentItemActionDTO(
                        consignment_item_id: $item->id,
                        quantity: $pending,
                        notes: $notes ?? 'Fechamento — retorno automático do saldo pendente',
                    );
                    $this->partialReturn($consignmentId, $dto);
                }
            }

            $consignment = $this->repository->findWithDetails($consignmentId);

            $operation = $this->recordOperation(
                $consignment,
                null,
                ConsignmentOperationType::Fechamento,
                0,
                $notes ?? 'Consignado fechado',
            );

            $consignment->update([
                'status' => ConsignmentStatus::Fechado,
                'closed_at' => now(),
            ]);

            event(new ConsignmentClosed($consignment->fresh()));
            event(new ConsignmentOperationRecorded($consignment, $operation));

            return $this->findDetails($consignment->id);
        });
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, ConsignmentOperation> */
    public function operations(string $consignmentId)
    {
        return $this->operations->forConsignment($consignmentId);
    }

    public function stockMovements(string $consignmentId)
    {
        return $this->stockMovements->forConsignment($consignmentId);
    }

    private function applyItemOperation(
        string $consignmentId,
        ConsignmentItemActionDTO $dto,
        ConsignmentOperationType $operationType,
        StockMovementType $movementType,
        string $quantityColumn,
        string $defaultNote,
    ): Consignment
    {
        return DB::transaction(function () use (
            $consignmentId,
            $dto,
            $operationType,
            $movementType,
            $quantityColumn,
            $defaultNote,
        )
        {
            $consignment = $this->loadForOperation($consignmentId);
            $item = $this->items->findForConsignment($consignmentId, $dto->consignment_item_id);
            $this->assertQuantityAvailable($item, $dto->quantity);

            $this->stock->assertSufficientResellerStock(
                $consignment->company_id,
                $item->product_id,
                $consignment->reseller_id,
                $dto->quantity
            );

            $operation = $this->recordOperation(
                $consignment,
                $item,
                $operationType,
                $dto->quantity,
                $dto->notes ?? $defaultNote
            );

            $this->stock->record(
                companyId: $consignment->company_id,
                productId: $item->product_id,
                type: $movementType,
                quantity: $dto->quantity,
                resellerId: $consignment->reseller_id,
                reference: $consignment,
                operation: $operation,
                notes: $dto->notes,
            );

            $item->increment($quantityColumn, $dto->quantity);
            $this->afterItemMutation($consignment, $item, $operation);

            return $this->findDetails($consignment->id);
        });
    }

    private function recordOperation(
        Consignment $consignment,
        ?ConsignmentItem $item,
        ConsignmentOperationType $type,
        int $quantity,
        ?string $notes = null,
        ?array $metadata = null,
    ): ConsignmentOperation
    {
        $operation = $this->operations->create([
            'company_id' => $consignment->company_id,
            'consignment_id' => $consignment->id,
            'consignment_item_id' => $item?->id,
            'product_id' => $item?->product_id,
            'operation_type' => $type,
            'quantity' => max(0, $quantity),
            'notes' => $notes,
            'metadata' => $metadata,
            'performed_by' => Auth::id(),
        ]);

        return $operation;
    }

    private function afterItemMutation(
        Consignment $consignment,
        ConsignmentItem $item,
        ConsignmentOperation $operation,
    ): void
    {
        $item->refresh();
        $consignment->refresh();

        $allPartial = $consignment->items->every(
            fn(ConsignmentItem $i) => $i->quantityPending() < $i->quantity
        );

        if ($allPartial && ! $consignment->isClosed())
        {
            $consignment->update(['status' => ConsignmentStatus::Parcial]);
        }

        $this->syncOverdueStatus($consignment);
        event(new ConsignmentOperationRecorded($consignment, $operation));
    }

    private function syncOverdueStatus(Consignment $consignment): void
    {
        if ($consignment->isClosed() || ! $consignment->isDispatched())
        {
            return;
        }

        if (
            $consignment->expected_return_at !== null
            && $consignment->expected_return_at->isPast()
            && $consignment->status !== ConsignmentStatus::Atrasado
        )
        {
            $consignment->update(['status' => ConsignmentStatus::Atrasado]);
        }
    }

    private function loadForOperation(string $consignmentId): Consignment
    {
        $consignment = $this->repository->findWithDetails($consignmentId);
        $this->assertCanModify($consignment);
        $this->assertDispatched($consignment);

        return $consignment;
    }

    private function assertCanModify(Consignment $consignment): void
    {
        if ($consignment->isClosed())
        {
            throw ValidationException::withMessages([
                'consignment' => ['Consignado já está fechado.'],
            ]);
        }
    }

    private function assertNotDispatched(Consignment $consignment): void
    {
        if ($consignment->isDispatched())
        {
            throw ValidationException::withMessages([
                'consignment' => ['Consignado já foi enviado.'],
            ]);
        }
    }

    private function assertDispatched(Consignment $consignment): void
    {
        if (! $consignment->isDispatched())
        {
            throw ValidationException::withMessages([
                'consignment' => ['É necessário realizar o envio antes desta operação.'],
            ]);
        }
    }

    private function assertQuantityAvailable(ConsignmentItem $item, int $quantity): void
    {
        if ($quantity <= 0)
        {
            throw ValidationException::withMessages([
                'quantity' => ['Quantidade deve ser maior que zero.'],
            ]);
        }

        if ($quantity > $item->quantityPending())
        {
            throw ValidationException::withMessages([
                'quantity' => ["Quantidade excede o pendente ({$item->quantityPending()})."],
            ]);
        }
    }
}
