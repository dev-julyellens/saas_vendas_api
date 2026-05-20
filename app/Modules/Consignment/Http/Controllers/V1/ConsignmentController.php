<?php

declare(strict_types=1);

namespace App\Modules\Consignment\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Consignment\DTOs\ConsignmentDTO;
use App\Modules\Consignment\DTOs\ConsignmentItemActionDTO;
use App\Modules\Consignment\Http\Requests\ConsignmentItemActionRequest;
use App\Modules\Consignment\Http\Requests\ConsignmentNotesRequest;
use App\Modules\Consignment\Http\Requests\StoreConsignmentRequest;
use App\Modules\Consignment\Http\Resources\ConsignmentOperationResource;
use App\Modules\Consignment\Http\Resources\ConsignmentResource;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Consignment\Services\ConsignmentService;
use App\Modules\Product\Http\Resources\StockMovementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsignmentController extends ApiController
{
    public function __construct(private ConsignmentService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Consignment::class);

        $paginator = $this->service->paginate(
            (int) $request->get('per_page', 15),
            $request->only(['status', 'reseller_id'])
        );

        return $this->success(
            ConsignmentResource::collection($paginator),
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function store(StoreConsignmentRequest $request): JsonResponse
    {
        $this->authorize('create', Consignment::class);

        $consignment = $this->service->store(ConsignmentDTO::fromArray($request->validated()));

        return $this->created(new ConsignmentResource($consignment));
    }

    public function show(Consignment $consignment): JsonResponse
    {
        $this->authorize('view', $consignment);

        return $this->success(new ConsignmentResource($this->service->findDetails($consignment->id)));
    }

    public function dispatchShipment(Consignment $consignment): JsonResponse
    {
        $this->authorize('operate', $consignment);

        return $this->success(
            new ConsignmentResource($this->service->dispatch($consignment->id)),
            'Envio consignado realizado.'
        );
    }

    public function partialSale(ConsignmentItemActionRequest $request, Consignment $consignment): JsonResponse
    {
        $this->authorize('operate', $consignment);

        return $this->success(
            new ConsignmentResource($this->service->partialSale(
                $consignment->id,
                ConsignmentItemActionDTO::fromArray($request->validated())
            )),
            'Venda parcial registrada.'
        );
    }

    public function partialReturn(ConsignmentItemActionRequest $request, Consignment $consignment): JsonResponse
    {
        $this->authorize('operate', $consignment);

        return $this->success(
            new ConsignmentResource($this->service->partialReturn(
                $consignment->id,
                ConsignmentItemActionDTO::fromArray($request->validated())
            )),
            'Devolução parcial registrada.'
        );
    }

    public function loss(ConsignmentItemActionRequest $request, Consignment $consignment): JsonResponse
    {
        $this->authorize('operate', $consignment);

        return $this->success(
            new ConsignmentResource($this->service->registerLoss(
                $consignment->id,
                ConsignmentItemActionDTO::fromArray($request->validated())
            )),
            'Perda registrada.'
        );
    }

    public function damage(ConsignmentItemActionRequest $request, Consignment $consignment): JsonResponse
    {
        $this->authorize('operate', $consignment);

        return $this->success(
            new ConsignmentResource($this->service->registerDamage(
                $consignment->id,
                ConsignmentItemActionDTO::fromArray($request->validated())
            )),
            'Avaria registrada.'
        );
    }

    public function divergence(ConsignmentItemActionRequest $request, Consignment $consignment): JsonResponse
    {
        $this->authorize('operate', $consignment);

        return $this->success(
            new ConsignmentResource($this->service->registerDivergence(
                $consignment->id,
                ConsignmentItemActionDTO::fromArray($request->validated())
            )),
            'Divergência registrada.'
        );
    }

    public function collect(ConsignmentNotesRequest $request, Consignment $consignment): JsonResponse
    {
        $this->authorize('operate', $consignment);

        return $this->success(
            new ConsignmentResource($this->service->collect($consignment->id, $request->input('notes'))),
            'Coleta registrada.'
        );
    }

    public function close(ConsignmentNotesRequest $request, Consignment $consignment): JsonResponse
    {
        $this->authorize('operate', $consignment);

        return $this->success(
            new ConsignmentResource($this->service->close($consignment->id, $request->input('notes'))),
            'Consignado fechado.'
        );
    }

    public function operations(Consignment $consignment): JsonResponse
    {
        $this->authorize('view', $consignment);

        return $this->success(
            ConsignmentOperationResource::collection($this->service->operations($consignment->id))
        );
    }

    public function movements(Consignment $consignment): JsonResponse
    {
        $this->authorize('view', $consignment);

        return $this->success(
            StockMovementResource::collection($this->service->stockMovements($consignment->id))
        );
    }
}
