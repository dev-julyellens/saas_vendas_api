<?php

declare(strict_types=1);

namespace App\Modules\Sale\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Sale\DTOs\SaleDTO;
use App\Modules\Sale\DTOs\SaleFilterDTO;
use App\Modules\Sale\Http\Requests\StoreSaleRequest;
use App\Modules\Sale\Http\Requests\UpdateSaleRequest;
use App\Modules\Sale\Http\Resources\SaleResource;
use App\Modules\Sale\Models\Sale;
use App\Modules\Sale\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends ApiController
{
    public function __construct(private SaleService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Sale::class);

        $filters = SaleFilterDTO::fromArray($request->only([
            'status',
            'reseller_id',
            'customer_id',
            'representative_id',
            'consignment_id',
            'code',
            'date_from',
            'date_to',
            'min_total',
            'max_total',
            'confirmed_only',
        ]));

        $paginator = $this->service->paginate(
            (int) $request->get('per_page', 15),
            $filters->toArray()
        );

        return $this->success(
            SaleResource::collection($paginator),
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $this->authorize('create', Sale::class);

        $sale = $this->service->store(SaleDTO::fromArray($request->validated()));

        return $this->created(new SaleResource($sale));
    }

    public function show(Sale $sale): JsonResponse
    {
        $this->authorize('view', $sale);

        return $this->success(new SaleResource($this->service->findDetails($sale->id)));
    }

    public function update(UpdateSaleRequest $request, Sale $sale): JsonResponse
    {
        $this->authorize('update', $sale);

        $updated = $this->service->updateFromDto($sale->id, SaleDTO::fromArray($request->validated()));

        return $this->success(new SaleResource($updated));
    }

    public function destroy(Sale $sale): JsonResponse
    {
        $this->authorize('delete', $sale);

        $this->service->delete($sale->id);

        return $this->success(message: 'Venda removida.');
    }

    public function confirm(Sale $sale): JsonResponse
    {
        $this->authorize('confirm', $sale);

        return $this->success(
            new SaleResource($this->service->confirm($sale->id)),
            'Venda confirmada.'
        );
    }

    public function cancel(Sale $sale): JsonResponse
    {
        $this->authorize('cancel', $sale);

        return $this->success(
            new SaleResource($this->service->cancel($sale->id)),
            'Venda cancelada.'
        );
    }

    public function dashboard(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Sale::class);

        return $this->success(
            $this->service->dashboard(
                $request->get('date_from'),
                $request->get('date_to')
            )
        );
    }

    public function report(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Sale::class);

        $filters = SaleFilterDTO::fromArray($request->only([
            'status',
            'reseller_id',
            'customer_id',
            'representative_id',
            'consignment_id',
            'code',
            'date_from',
            'date_to',
            'min_total',
            'max_total',
        ]));

        return $this->success($this->service->report($filters));
    }
}
