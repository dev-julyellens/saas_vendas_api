<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Repositories;

use App\Core\Enums\ConsignmentOperationType;
use App\Core\Enums\ConsignmentStatus;
use App\Core\Enums\FinancialTransactionStatus;
use App\Core\Enums\SaleStatus;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Consignment\Models\ConsignmentOperation;
use App\Modules\Financial\Models\FinancialTransaction;
use App\Modules\Product\Models\Product;
use App\Modules\Sale\Models\Sale;
use App\Modules\Sale\Models\SaleItem;
use Carbon\Carbon;

class AnalyticsRepository
{
    public function salesSummary(string $companyId, Carbon $from, Carbon $to): array
    {
        $base = Sale::query()
            ->where('company_id', $companyId)
            ->where(function ($q) use ($from, $to)
            {
                $q->whereBetween('sold_at', [$from, $to])
                    ->orWhere(function ($q2) use ($from, $to)
                    {
                        $q2->whereNull('sold_at')
                            ->whereBetween('created_at', [$from, $to]);
                    });
            });

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

    /** @return list<array{date: string, revenue: float, count: int}> */
    public function salesByDay(string $companyId, Carbon $from, Carbon $to): array
    {
        return Sale::query()
            ->where('company_id', $companyId)
            ->where('status', SaleStatus::Confirmed)
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('DATE(sold_at) as sale_date, SUM(total) as revenue, COUNT(*) as sales_count')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->map(fn($row) => [
                'date' => (string) $row->sale_date,
                'revenue' => (float) $row->revenue,
                'count' => (int) $row->sales_count,
            ])
            ->all();
    }

    /** @return list<array{status: string, count: int}> */
    public function salesByStatus(string $companyId, Carbon $from, Carbon $to): array
    {
        return Sale::query()
            ->where('company_id', $companyId)
            ->where(function ($q) use ($from, $to)
            {
                $q->whereBetween('sold_at', [$from, $to])
                    ->orWhere(function ($q2) use ($from, $to)
                    {
                        $q2->whereNull('sold_at')
                            ->whereBetween('created_at', [$from, $to]);
                    });
            })
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->map(fn($row) => [
                'status' => $row->status instanceof SaleStatus ? $row->status->value : (string) $row->status,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /** @return list<array{reseller_id: string, reseller_name: string|null, sales_count: int, revenue: float}> */
    public function topResellers(string $companyId, Carbon $from, Carbon $to, int $limit = 8): array
    {
        return Sale::query()
            ->where('company_id', $companyId)
            ->where('status', SaleStatus::Confirmed)
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('reseller_id, COUNT(*) as sales_count, SUM(total) as revenue')
            ->groupBy('reseller_id')
            ->orderByDesc('revenue')
            ->limit($limit)
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

    /** @return list<array{representative_id: string, representative_name: string|null, sales_count: int, revenue: float}> */
    public function topRepresentatives(string $companyId, Carbon $from, Carbon $to, int $limit = 8): array
    {
        return Sale::query()
            ->where('company_id', $companyId)
            ->where('status', SaleStatus::Confirmed)
            ->whereNotNull('representative_id')
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw('representative_id, COUNT(*) as sales_count, SUM(total) as revenue')
            ->groupBy('representative_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->with('representative:id,name')
            ->get()
            ->map(fn($row) => [
                'representative_id' => $row->representative_id,
                'representative_name' => $row->representative?->name,
                'sales_count' => (int) $row->sales_count,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    /** @return list<array{product_id: string, sku: string|null, name: string|null, quantity_sold: int, revenue: float}> */
    public function topProducts(string $companyId, Carbon $from, Carbon $to, int $limit = 8): array
    {
        $salesQuery = Sale::query()
            ->where('company_id', $companyId)
            ->where('status', SaleStatus::Confirmed)
            ->whereBetween('sold_at', [$from, $to])
            ->select('id');

        return SaleItem::query()
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

    public function consignmentMetrics(string $companyId, Carbon $from, Carbon $to): array
    {
        $consignedQty = (int) ConsignmentOperation::query()
            ->where('company_id', $companyId)
            ->where('operation_type', ConsignmentOperationType::Envio)
            ->whereBetween('created_at', [$from, $to])
            ->sum('quantity');

        $returnedQty = (int) ConsignmentOperation::query()
            ->where('company_id', $companyId)
            ->where('operation_type', ConsignmentOperationType::DevolucaoParcial)
            ->whereBetween('created_at', [$from, $to])
            ->sum('quantity');

        $byStatus = Consignment::query()
            ->where('company_id', $companyId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'consigned_quantity' => $consignedQty,
            'returned_quantity' => $returnedQty,
            'open_count' => (int) ($byStatus[ConsignmentStatus::Aberto->value] ?? 0)
                + (int) ($byStatus[ConsignmentStatus::Parcial->value] ?? 0),
            'overdue_count' => (int) ($byStatus[ConsignmentStatus::Atrasado->value] ?? 0),
            'closed_count' => (int) ($byStatus[ConsignmentStatus::Fechado->value] ?? 0),
        ];
    }

    public function delinquencyAmount(string $companyId): float
    {
        return (float) FinancialTransaction::query()
            ->where('company_id', $companyId)
            ->whereIn('status', [
                FinancialTransactionStatus::Overdue,
                FinancialTransactionStatus::Pending,
            ])
            ->where(function ($q)
            {
                $q->where('status', FinancialTransactionStatus::Overdue)
                    ->orWhere(function ($q2)
                    {
                        $q2->where('status', FinancialTransactionStatus::Pending)
                            ->whereDate('due_date', '<', now());
                    });
            })
            ->sum('amount');
    }

    public function delinquencyCount(string $companyId): int
    {
        return FinancialTransaction::query()
            ->where('company_id', $companyId)
            ->whereIn('status', [
                FinancialTransactionStatus::Overdue,
                FinancialTransactionStatus::Pending,
            ])
            ->where(function ($q)
            {
                $q->where('status', FinancialTransactionStatus::Overdue)
                    ->orWhere(function ($q2)
                    {
                        $q2->where('status', FinancialTransactionStatus::Pending)
                            ->whereDate('due_date', '<', now());
                    });
            })
            ->count();
    }

    public function idleProductsCount(string $companyId, Carbon $from, Carbon $to): int
    {
        $soldProductIds = SaleItem::query()
            ->whereIn('sale_id', function ($q) use ($companyId, $from, $to)
            {
                $q->select('id')
                    ->from('sales')
                    ->where('company_id', $companyId)
                    ->where('status', SaleStatus::Confirmed->value)
                    ->whereBetween('sold_at', [$from, $to]);
            })
            ->distinct()
            ->pluck('product_id');

        return Product::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->when($soldProductIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $soldProductIds))
            ->count();
    }

    /** @return list<array{product_id: string, sku: string|null, name: string|null, days_without_sale: int}> */
    public function idleProducts(string $companyId, Carbon $from, Carbon $to, int $limit = 8): array
    {
        $soldProductIds = SaleItem::query()
            ->whereIn('sale_id', function ($q) use ($companyId, $from, $to)
            {
                $q->select('id')
                    ->from('sales')
                    ->where('company_id', $companyId)
                    ->where('status', SaleStatus::Confirmed->value)
                    ->whereBetween('sold_at', [$from, $to]);
            })
            ->distinct()
            ->pluck('product_id');

        return Product::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->when($soldProductIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $soldProductIds))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'sku', 'name', 'created_at'])
            ->map(fn($p) => [
                'product_id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'days_without_sale' => (int) $p->created_at?->diffInDays($to) ?? 0,
            ])
            ->all();
    }
}
