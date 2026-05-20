<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Consignment\Models\ConsignmentItem;
use App\Modules\Sale\Models\SaleItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_category_id',
        'sku',
        'name',
        'description',
        'unit_price',
        'cost_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function consignmentItems(): HasMany
    {
        return $this->hasMany(ConsignmentItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Saldo no estoque da empresa (reseller_id IS NULL).
     */
    public function companyStockBalance(): int
    {
        return $this->stockBalanceFor(null);
    }

    /**
     * Saldo no revendedor (consignado).
     */
    public function resellerStockBalance(string $resellerId): int
    {
        return $this->stockBalanceFor($resellerId);
    }

    public function stockBalanceFor(?string $resellerId): int
    {
        $inbound = [
            \App\Core\Enums\StockMovementType::Entrada->value,
            \App\Core\Enums\StockMovementType::Devolucao->value,
        ];

        $query = $this->stockMovements()
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN movement_type IN (?, ?) THEN quantity ELSE -quantity END), 0) as balance',
                $inbound
            )
            ->when(
                $resellerId === null,
                fn($q) => $q->whereNull('reseller_id'),
                fn($q) => $q->where('reseller_id', $resellerId)
            );

        return (int) $query->value('balance');
    }
}
