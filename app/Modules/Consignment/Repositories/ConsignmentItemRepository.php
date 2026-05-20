<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Consignment\Models\ConsignmentItem;

class ConsignmentItemRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ConsignmentItem);
    }

    public function findForConsignment(string $consignmentId, string $itemId): ConsignmentItem
    {
        return ConsignmentItem::query()
            ->where('consignment_id', $consignmentId)
            ->with('product')
            ->findOrFail($itemId);
    }
}
