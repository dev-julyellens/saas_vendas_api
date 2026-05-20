<?php

declare(strict_types=1);

namespace App\Modules\Reseller\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Reseller\Http\Requests\StoreResellerRequest;
use App\Modules\Reseller\Http\Requests\UpdateResellerRequest;
use App\Modules\Reseller\Http\Resources\ResellerResource;
use App\Modules\Reseller\Models\Reseller;
use App\Modules\Reseller\Services\ResellerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResellerController extends ApiController
{
    public function __construct(private ResellerService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Reseller::class);

        $paginator = $this->service->paginate(
            (int) $request->get('per_page', 15),
            $request->only(['search', 'is_active', 'representative_id'])
        );

        return $this->success(
            ResellerResource::collection($paginator),
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function store(StoreResellerRequest $request): JsonResponse
    {
        $this->authorize('create', Reseller::class);

        return $this->created(new ResellerResource($this->service->store($request->validated())));
    }

    public function show(Reseller $reseller): JsonResponse
    {
        $this->authorize('view', $reseller);

        return $this->success(new ResellerResource($this->service->find($reseller->id)));
    }

    public function update(UpdateResellerRequest $request, Reseller $reseller): JsonResponse
    {
        $this->authorize('update', $reseller);

        return $this->success(
            new ResellerResource($this->service->update($reseller->id, $request->validated()))
        );
    }

    public function destroy(Reseller $reseller): JsonResponse
    {
        $this->authorize('delete', $reseller);

        $this->service->delete($reseller->id);

        return $this->success(message: 'Revendedor removido.');
    }
}
