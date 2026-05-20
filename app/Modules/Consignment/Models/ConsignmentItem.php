<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Models;

use App\Core\Models\BaseLineModel;
use App\Modules\Product\Models\Product;
use App\Modules\Sale\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentItem extends BaseLineModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'consignment_id',
        'product_id',
        'quantity',
        'quantity_sold',
        'quantity_returned',
        'quantity_lost',
        'quantity_damaged',
        'quantity_divergence',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'quantity_sold' => 'integer',
            'quantity_returned' => 'integer',
            'quantity_lost' => 'integer',
            'quantity_damaged' => 'integer',
            'quantity_divergence' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function consignment(): BelongsTo
    {
        return $this->belongsTo(Consignment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(ConsignmentOperation::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function quantityPending(): int
    {
        return max(0, (int) $this->quantity
            - $this->quantity_sold
            - $this->quantity_returned
            - $this->quantity_lost
            - $this->quantity_damaged
            - $this->quantity_divergence);
    }

    public function quantityAccounted(): int
    {
        return $this->quantity_sold
            + $this->quantity_returned
            + $this->quantity_lost
            + $this->quantity_damaged
            + $this->quantity_divergence;
    }
}
