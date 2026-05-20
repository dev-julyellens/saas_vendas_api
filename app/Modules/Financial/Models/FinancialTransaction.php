<?php

namespace App\Modules\Financial\Models;

use App\Core\Models\BaseModel;

class FinancialTransaction extends BaseModel
{
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
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'date',
        ];
    }
}
