<?php

declare(strict_types=1);

namespace App\Modules\Commission\Models;

use App\Core\Enums\CommissionStatus;
use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Representative\Models\Representative;
use App\Modules\Sale\Models\Sale;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'sale_id',
        'representative_id',
        'base_amount',
        'rate',
        'amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommissionStatus::class,
            'base_amount' => 'decimal:2',
            'rate' => 'decimal:4',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class);
    }
}
