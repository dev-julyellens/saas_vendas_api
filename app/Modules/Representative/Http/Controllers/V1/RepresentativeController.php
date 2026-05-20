<?php

declare(strict_types=1);

namespace App\Modules\Representative\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Representative\Http\Resources\RepresentativeResource;
use App\Modules\Representative\Models\Representative;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepresentativeController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Representative::query()->orderBy('name');

        if ($request->boolean('active_only', true))
        {
            $query->where('is_active', true);
        }

        if ($search = $request->get('search'))
        {
            $query->where('name', 'ilike', "%{$search}%");
        }

        $paginator = $query->paginate((int) $request->get('per_page', 50));

        return $this->success(
            RepresentativeResource::collection($paginator),
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }
}
