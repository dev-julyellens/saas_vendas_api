<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Consignment\Models\ConsignmentOperation;
use Illuminate\Database\Eloquent\Collection;

class ConsignmentOperationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ConsignmentOperation);
    }

    /** @return Collection<int, ConsignmentOperation> */
    public function forConsignment(string $consignmentId): Collection
    {
        return ConsignmentOperation::query()
            ->where('consignment_id', $consignmentId)
            ->with(['product', 'performedBy', 'item'])
            ->orderBy('created_at')
            ->get();
    }
}
