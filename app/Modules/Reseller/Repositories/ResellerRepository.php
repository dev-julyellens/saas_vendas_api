<?php

declare(strict_types=1);

namespace App\Modules\Reseller\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Reseller\Models\Reseller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ResellerRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Reseller);
    }

    public function paginateWithFilters(int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->applyFilters(Reseller::query(), $filters)
            ->with('representative:id,name')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** @return Builder<Reseller> */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn($q, $v) => $q->where(function ($q2) use ($v)
            {
                $q2->where('name', 'ilike', "%{$v}%")
                    ->orWhere('document', 'ilike', "%{$v}%")
                    ->orWhere('email', 'ilike', "%{$v}%");
            }))
            ->when(isset($filters['is_active']), fn($q) => $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->when($filters['representative_id'] ?? null, fn($q, $v) => $q->where('representative_id', $v));
    }
}
