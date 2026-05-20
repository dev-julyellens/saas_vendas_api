<?php

declare(strict_types=1);

namespace App\Modules\Customer\Models;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Reseller\Models\Reseller;
use App\Modules\Sale\Models\Sale;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'reseller_id',
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

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
