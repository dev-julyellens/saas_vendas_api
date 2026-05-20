<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Consignment\Models\Consignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ConsignmentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Consignment);
    }

    public function paginateWithRelations(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Consignment::query()
            ->with(['reseller', 'representative'])
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['reseller_id'] ?? null, fn($q, $id) => $q->where('reseller_id', $id))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findWithDetails(string $id): Consignment
    {
        return Consignment::query()
            ->with([
                'items.product',
                'reseller',
                'representative',
                'operations.performedBy',
                'operations.product',
            ])
            ->findOrFail($id);
    }

    public function nextCode(string $companyId): string
    {
        $last = Consignment::query()
            ->where('company_id', $companyId)
            ->where('code', 'like', 'CONS-%')
            ->orderByDesc('code')
            ->value('code');

        if ($last === null)
        {
            return 'CONS-0001';
        }

        $number = (int) substr($last, 5);

        return 'CONS-' . str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
    }
}
