<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Models;

use App\Core\Enums\ConsignmentStatus;
use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Core\Models\Concerns\HasStockMovements;
use App\Modules\Representative\Models\Representative;
use App\Modules\Reseller\Models\Reseller;
use App\Modules\ReturnOrder\Models\ReturnOrder;
use App\Modules\Sale\Models\Sale;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consignment extends BaseModel
{
    use HasFactory;
    use HasStockMovements;

    protected $fillable = [
        'company_id',
        'reseller_id',
        'representative_id',
        'code',
        'status',
        'consigned_at',
        'expected_return_at',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ConsignmentStatus::class,
            'consigned_at' => 'date',
            'expected_return_at' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConsignmentItem::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnOrder::class);
    }
}
