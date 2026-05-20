<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Modules\Rbac\Http\Resources\RoleResource;
use App\Modules\Rbac\Services\RbacService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends ApiController
{
    public function __construct(private RbacService $rbac)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Modules\Rbac\Models\Role::class);

        return $this->success(RoleResource::collection($this->rbac->listRoles($request->user())));
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $role = $this->rbac->findRole($request->user(), $id);
        $this->authorize('view', $role);

        return $this->success(new RoleResource($role->load('permissions')));
    }
}
