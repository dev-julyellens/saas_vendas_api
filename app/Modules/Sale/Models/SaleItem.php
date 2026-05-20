<?php

declare(strict_types=1);

namespace App\Modules\Sale\Models;

use App\Core\Models\BaseLineModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Consignment\Models\ConsignmentItem;
use App\Modules\Product\Models\Product;
use App\Modules\ReturnOrder\Models\ReturnItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends BaseLineModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'sale_id',
        'product_id',
        'consignment_item_id',
        'quantity',
        'unit_price',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function consignmentItem(): BelongsTo
    {
        return $this->belongsTo(ConsignmentItem::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }
}
