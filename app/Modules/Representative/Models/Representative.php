<?php

declare(strict_types=1);

namespace App\Modules\Representative\Models;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Commission\Models\Commission;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Reseller\Models\Reseller;
use App\Modules\Sale\Models\Sale;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Representative extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'document',
        'email',
        'phone',
        'commission_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function resellers(): HasMany
    {
        return $this->hasMany(Reseller::class);
    }

    public function consignments(): HasMany
    {
        return $this->hasMany(Consignment::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }
}
