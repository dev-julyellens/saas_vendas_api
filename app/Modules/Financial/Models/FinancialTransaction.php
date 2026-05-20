<?php

declare(strict_types=1);

namespace App\Modules\Financial\Models;

use App\Core\Enums\FinancialTransactionStatus;
use App\Core\Enums\FinancialTransactionType;
use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FinancialTransaction extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'reference_type',
        'reference_id',
        'type',
        'category',
        'amount',
        'due_date',
        'paid_at',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => FinancialTransactionType::class,
            'status' => FinancialTransactionStatus::class,
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'date',
        ];
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
