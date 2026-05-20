<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Core\Enums\StockMovementType;
use App\Modules\Consignment\Models\ConsignmentOperation;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\StockMovement;
use App\Modules\Product\Repositories\StockMovementRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Único ponto de escrita em stock_movements — saldo nunca é atualizado diretamente.
 */
class StockMovementService
{
    public function __construct(private StockMovementRepository $movements)
    {
    }

    public function record(
        string $companyId,
        string $productId,
        StockMovementType $type,
        int $quantity,
        ?string $resellerId = null,
        ?Model $reference = null,
        ?ConsignmentOperation $operation = null,
        ?string $notes = null,
        ?float $unitCost = null,
    ): StockMovement
    {
        if ($quantity <= 0)
        {
            throw ValidationException::withMessages([
                'quantity' => ['Quantidade deve ser maior que zero.'],
            ]);
        }

        return $this->movements->create([
            'company_id' => $companyId,
            'product_id' => $productId,
            'reseller_id' => $resellerId,
            'movement_type' => $type,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'consignment_operation_id' => $operation?->id,
            'notes' => $notes,
            'occurred_at' => now(),
            'created_by' => Auth::id(),
        ]);
    }

    public function balance(string $companyId, string $productId, ?string $resellerId = null): int
    {
        $movements = StockMovement::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->when(
                $resellerId === null,
                fn($q) => $q->whereNull('reseller_id'),
                fn($q) => $q->where('reseller_id', $resellerId)
            )
            ->get(['movement_type', 'quantity']);

        return $movements->sum(
            fn(StockMovement $m) => $this->signedQuantity($m->movement_type, $resellerId !== null) * (int) $m->quantity
        );
    }

    public function assertSufficientCompanyStock(Product $product, int $quantity): void
    {
        $available = $this->balance($product->company_id, $product->id, null);

        if ($available < $quantity)
        {
            throw ValidationException::withMessages([
                'quantity' => ["Estoque insuficiente na empresa. Disponível: {$available}."],
            ]);
        }
    }

    public function assertSufficientResellerStock(
        string $companyId,
        string $productId,
        string $resellerId,
        int $quantity,
    ): void
    {
        $available = $this->balance($companyId, $productId, $resellerId);

        if ($available < $quantity)
        {
            throw ValidationException::withMessages([
                'quantity' => ["Estoque consignado insuficiente no revendedor. Disponível: {$available}."],
            ]);
        }
    }

    /**
     * @param  bool  $atReseller  true = estoque no revendedor; false = estoque da empresa
     */
    public function signedQuantity(StockMovementType $type, bool $atReseller): int
    {
        return match ($type)
        {
            StockMovementType::Entrada => 1,
            StockMovementType::Saida => -1,
            StockMovementType::Consignado => $atReseller ? 1 : -1,
            StockMovementType::Devolucao => $atReseller ? -1 : 1,
            StockMovementType::Venda, StockMovementType::Perda, StockMovementType::Avaria => -1,
        };
    }
}
