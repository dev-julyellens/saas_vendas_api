<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Analytics\Services\AnalyticsService;
use App\Modules\Sale\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends ApiController
{
    public function __construct(private AnalyticsService $service)
    {
    }

    public function dashboard(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Sale::class);

        return $this->success(
            $this->service->dashboard(
                $request->get('date_from'),
                $request->get('date_to'),
            )
        );
    }
}
