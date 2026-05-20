<?php

declare(strict_types=1);

namespace App\Modules\Commission\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Commission\Models\Commission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CommissionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Commission);
    }

    public function paginateWithFilters(int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->applyFilters(Commission::query(), $filters)
            ->with([
                'representative:id,name',
                'sale:id,code,total,reseller_id',
                'sale.reseller:id,name',
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findWithRelations(string $id): Commission
    {
        return Commission::query()
            ->with([
                'representative:id,name,commission_rate',
                'sale:id,code,total,reseller_id,sold_at',
                'sale.reseller:id,name',
            ])
            ->findOrFail($id);
    }

    /** @return Builder<Commission> */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['representative_id'] ?? null, fn($q, $v) => $q->where('representative_id', $v))
            ->when($filters['sale_id'] ?? null, fn($q, $v) => $q->where('sale_id', $v));
    }
}
