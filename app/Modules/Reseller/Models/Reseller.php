<?php

declare(strict_types=1);

namespace App\Modules\Reseller\Models;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Customer\Models\Customer;
use App\Modules\Product\Models\StockMovement;
use App\Modules\Representative\Models\Representative;
use App\Modules\ReturnOrder\Models\ReturnOrder;
use App\Modules\Sale\Models\Sale;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reseller extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'representative_id',
        'name',
        'document',
        'email',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function consignments(): HasMany
    {
        return $this->hasMany(Consignment::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnOrder::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
