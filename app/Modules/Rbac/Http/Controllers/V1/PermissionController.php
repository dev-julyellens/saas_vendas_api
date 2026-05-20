<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Rbac\Http\Resources\PermissionResource;
use App\Modules\Rbac\Services\RbacService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends ApiController
{
    public function __construct(private RbacService $rbac)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Modules\Rbac\Models\Role::class);

        $permissions = $this->rbac->listPermissions($request->user());

        return $this->success(PermissionResource::collection($permissions));
    }
}
