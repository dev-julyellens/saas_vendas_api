<?php

declare(strict_types=1);

namespace App\Modules\Commission\Services;

use App\Core\Enums\CommissionStatus;
use App\Modules\Commission\Models\Commission;
use App\Modules\Commission\Repositories\CommissionRepository;
use App\Modules\Representative\Models\Representative;
use App\Modules\Sale\Models\Sale;

class CommissionService
{
    public function __construct(private CommissionRepository $commissions)
    {
    }

    public function createFromSale(Sale $sale): ?Commission
    {
        if ($sale->representative_id === null)
        {
            return null;
        }

        $representative = Representative::query()->findOrFail($sale->representative_id);
        $rate = (float) $representative->commission_rate;
        $base = (float) $sale->total;
        $amount = round($base * $rate, 2);

        return $this->commissions->create([
            'company_id' => $sale->company_id,
            'sale_id' => $sale->id,
            'representative_id' => $sale->representative_id,
            'base_amount' => $base,
            'rate' => $rate,
            'amount' => $amount,
            'status' => CommissionStatus::Pending,
        ]);
    }

    public function cancelForSale(Sale $sale): void
    {
        Commission::query()
            ->where('sale_id', $sale->id)
            ->where('status', CommissionStatus::Pending)
            ->update(['status' => CommissionStatus::Cancelled]);
    }

    public function paginate(int $perPage, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->commissions->paginateWithFilters($perPage, $filters);
    }

    public function find(string $id): Commission
    {
        return $this->commissions->findWithRelations($id);
    }

    public function updateStatus(string $id, CommissionStatus $status): Commission
    {
        $commission = $this->commissions->findOrFail($id);
        $data = ['status' => $status];

        if ($status === CommissionStatus::Paid)
        {
            $data['paid_at'] = now();
        }

        return $this->commissions->update($id, $data);
    }
}
