<?php

declare(strict_types=1);

namespace App\Modules\Product\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Product\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;

class StockMovementRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new StockMovement);
    }

    /** @return Collection<int, StockMovement> */
    public function forConsignment(string $consignmentId): Collection
    {
        $operationIds = \App\Modules\Consignment\Models\ConsignmentOperation::query()
            ->where('consignment_id', $consignmentId)
            ->pluck('id');

        return StockMovement::query()
            ->where(function ($q) use ($consignmentId, $operationIds)
            {
                $q->where(function ($q2) use ($consignmentId)
                {
                    $q2->where('reference_type', \App\Modules\Consignment\Models\Consignment::class)
                        ->where('reference_id', $consignmentId);
                });

                if ($operationIds->isNotEmpty())
                {
                    $q->orWhereIn('consignment_operation_id', $operationIds);
                }
            })
            ->orderByDesc('occurred_at')
            ->get();
    }
}
