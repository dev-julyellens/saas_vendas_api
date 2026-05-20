<?php

declare(strict_types=1);

namespace App\Modules\Sale\Repositories;

use App\Core\Enums\SaleStatus;
use App\Core\Repositories\BaseRepository;
use App\Modules\Sale\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SaleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Sale);
    }

    public function paginateWithFilters(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->applyFilters(Sale::query(), $filters)
            ->with(['reseller', 'customer', 'representative', 'items.product'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findWithDetails(string $id): Sale
    {
        return Sale::query()
            ->with([
                'items.product',
                'reseller',
                'customer',
                'representative',
                'consignment',
                'commission',
            ])
            ->findOrFail($id);
    }

    public function nextCode(string $companyId): string
    {
        $last = Sale::query()
            ->where('company_id', $companyId)
            ->where('code', 'like', 'VND-%')
            ->orderByDesc('code')
            ->value('code');

        if ($last === null)
        {
            return 'VND-0001';
        }

        $number = (int) substr($last, 4);

        return 'VND-' . str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
    }

    /** @return Builder<Sale> */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['reseller_id'] ?? null, fn($q, $v) => $q->where('reseller_id', $v))
            ->when($filters['customer_id'] ?? null, fn($q, $v) => $q->where('customer_id', $v))
            ->when($filters['representative_id'] ?? null, fn($q, $v) => $q->where('representative_id', $v))
            ->when($filters['consignment_id'] ?? null, fn($q, $v) => $q->where('consignment_id', $v))
            ->when($filters['code'] ?? null, fn($q, $v) => $q->where('code', 'like', '%' . $v . '%'))
            ->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('sold_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn($q, $v) => $q->whereDate('sold_at', '<=', $v))
            ->when($filters['min_total'] ?? null, fn($q, $v) => $q->where('total', '>=', $v))
            ->when($filters['max_total'] ?? null, fn($q, $v) => $q->where('total', '<=', $v))
            ->when(
                filter_var($filters['confirmed_only'] ?? false, FILTER_VALIDATE_BOOLEAN),
                fn($q) => $q->where('status', SaleStatus::Confirmed)
            );
    }

    public function dashboardStats(string $companyId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $base = Sale::query()->where('company_id', $companyId);

        if ($from)
        {
            $base->where(function ($q) use ($from, $to)
            {
                $q->whereBetween('sold_at', [$from, $to ?? now()])
                    ->orWhere(function ($q2) use ($from, $to)
                    {
                        $q2->whereNull('sold_at')
                            ->whereBetween('created_at', [$from, $to ?? now()]);
                    });
            });
        }

        $confirmed = (clone $base)->where('status', SaleStatus::Confirmed);

        return [
            'total_sales' => (clone $base)->count(),
            'confirmed_sales' => (clone $confirmed)->count(),
            'pending_sales' => (clone $base)->whereIn('status', [SaleStatus::Draft, SaleStatus::Pending])->count(),
            'cancelled_sales' => (clone $base)->where('status', SaleStatus::Cancelled)->count(),
            'revenue_total' => (float) (clone $confirmed)->sum('total'),
            'average_ticket' => (float) ((clone $confirmed)->avg('total') ?? 0),
        ];
    }

    public function reportByReseller(string $companyId, array $filters = []): array
    {
        $query = $this->applyFilters(
            Sale::query()->where('company_id', $companyId)->where('status', SaleStatus::Confirmed),
            $filters
        );

        return $query
            ->selectRaw('reseller_id, COUNT(*) as sales_count, SUM(total) as revenue')
            ->groupBy('reseller_id')
            ->with('reseller:id,name')
            ->get()
            ->map(fn($row) => [
                'reseller_id' => $row->reseller_id,
                'reseller_name' => $row->reseller?->name,
                'sales_count' => (int) $row->sales_count,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    public function reportTopProducts(string $companyId, array $filters = [], int $limit = 10): array
    {
        $salesQuery = $this->applyFilters(
            Sale::query()->where('company_id', $companyId)->where('status', SaleStatus::Confirmed),
            $filters
        )->select('id');

        return \App\Modules\Sale\Models\SaleItem::query()
            ->whereIn('sale_id', $salesQuery)
            ->selectRaw('product_id, SUM(quantity) as qty, SUM(total) as revenue')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->with('product:id,sku,name')
            ->get()
            ->map(fn($row) => [
                'product_id' => $row->product_id,
                'sku' => $row->product?->sku,
                'name' => $row->product?->name,
                'quantity_sold' => (int) $row->qty,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }
}
