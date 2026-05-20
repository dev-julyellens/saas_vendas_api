<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

use App\Core\Tenant\TenantContext;
use App\Modules\Analytics\Repositories\AnalyticsRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    private const CACHE_TTL_SECONDS = 300;

    public function __construct(private AnalyticsRepository $repository)
    {
    }

    public function dashboard(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $companyId = (string) TenantContext::companyId();
        $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->startOfMonth();
        $to = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();

        $cacheKey = sprintf(
            'analytics:%s:%s:%s',
            $companyId,
            $from->toDateString(),
            $to->toDateString(),
        );

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($companyId, $from, $to)
        {
            $sales = $this->repository->salesSummary($companyId, $from, $to);
            $consignment = $this->repository->consignmentMetrics($companyId, $from, $to);

            return [
                'period' => [
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                ],
                'kpis' => [
                    'sales_count' => $sales['total_sales'],
                    'confirmed_sales' => $sales['confirmed_sales'],
                    'pending_sales' => $sales['pending_sales'],
                    'cancelled_sales' => $sales['cancelled_sales'],
                    'revenue_total' => $sales['revenue_total'],
                    'average_ticket' => $sales['average_ticket'],
                    'consigned_products_qty' => $consignment['consigned_quantity'],
                    'returned_products_qty' => $consignment['returned_quantity'],
                    'overdue_consignments' => $consignment['overdue_count'],
                    'delinquency_amount' => $this->repository->delinquencyAmount($companyId),
                    'delinquency_count' => $this->repository->delinquencyCount($companyId),
                    'idle_products_count' => $this->repository->idleProductsCount($companyId, $from, $to),
                ],
                'sales_by_day' => $this->repository->salesByDay($companyId, $from, $to),
                'sales_by_status' => $this->repository->salesByStatus($companyId, $from, $to),
                'top_resellers' => $this->repository->topResellers($companyId, $from, $to),
                'top_representatives' => $this->repository->topRepresentatives($companyId, $from, $to),
                'top_products' => $this->repository->topProducts($companyId, $from, $to),
                'idle_products' => $this->repository->idleProducts($companyId, $from, $to),
                'consignment' => $consignment,
            ];
        });
    }
}
