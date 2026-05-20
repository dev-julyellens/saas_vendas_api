<?php

declare(strict_types=1);

namespace App\Core\Models\Concerns;

use App\Core\Enums\StockMovementType;
use App\Modules\Product\Models\StockMovement;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

/**
 * Saldo derivado de movimentações — nunca coluna de estoque fixo.
 */
trait HasStockMovements
{
    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    /**
     * Saldo agregado por produto (e opcionalmente revendedor).
     */
    public static function stockBalanceQuery(string $companyId, ?string $resellerId = null): \Illuminate\Database\Query\Builder
    {
        $inbound = StockMovementType::Entrada->value . "','" . StockMovementType::Devolucao->value;
        $driver = DB::connection()->getDriverName();

        $signedExpression = $driver === 'pgsql'
            ? "CASE WHEN movement_type::text IN ('{$inbound}') THEN quantity ELSE -quantity END"
            : "CASE WHEN movement_type IN ('{$inbound}') THEN quantity ELSE -quantity END";

        return DB::table('stock_movements')
            ->selectRaw("product_id, COALESCE(SUM({$signedExpression}), 0) as balance")
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->when(
                $resellerId === null,
                fn($q) => $q->whereNull('reseller_id'),
                fn($q) => $q->where('reseller_id', $resellerId)
            )
            ->groupBy('product_id');
    }
}
