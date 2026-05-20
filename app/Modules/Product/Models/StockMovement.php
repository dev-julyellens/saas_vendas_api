<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Core\Enums\StockMovementType;
use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Modules\Reseller\Models\Reseller;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Fonte única de verdade do estoque — saldo = SUM(movimentações com sinal).
 */
class StockMovement extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_id',
        'reseller_id',
        'movement_type',
        'quantity',
        'unit_cost',
        'reference_type',
        'reference_id',
        'notes',
        'occurred_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => StockMovementType::class,
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signedQuantity(): int
    {
        return $this->movement_type->signedQuantity((int) $this->quantity);
    }
}
