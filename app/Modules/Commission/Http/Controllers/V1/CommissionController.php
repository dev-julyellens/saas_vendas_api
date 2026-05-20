<?php

declare(strict_types=1);

namespace App\Modules\Commission\Http\Controllers\V1;

use App\Core\Enums\CommissionStatus;
use App\Core\Http\Controllers\ApiController;
use App\Modules\Commission\Http\Requests\UpdateCommissionStatusRequest;
use App\Modules\Commission\Http\Resources\CommissionResource;
use App\Modules\Commission\Models\Commission;
use App\Modules\Commission\Services\CommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionController extends ApiController
{
    public function __construct(private CommissionService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Commission::class);

        $paginator = $this->service->paginate(
            (int) $request->get('per_page', 15),
            $request->only(['status', 'representative_id', 'sale_id'])
        );

        return $this->success(
            CommissionResource::collection($paginator),
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function show(Commission $commission): JsonResponse
    {
        $this->authorize('view', $commission);

        return $this->success(new CommissionResource($this->service->find($commission->id)));
    }

    public function updateStatus(UpdateCommissionStatusRequest $request, Commission $commission): JsonResponse
    {
        $this->authorize('update', $commission);

        $status = CommissionStatus::from($request->input('status'));
        $updated = $this->service->updateStatus($commission->id, $status);

        return $this->success(new CommissionResource($updated), 'Status da comissão atualizado.');
    }
}
