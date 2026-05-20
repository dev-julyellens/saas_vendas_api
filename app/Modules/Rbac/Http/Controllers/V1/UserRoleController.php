<?php

declare(strict_types=1);

namespace App\Modules\Rbac\Http\Controllers\V1;

use App\Core\Http\Controllers\ApiController;
use App\Models\User;
use App\Modules\Auth\Http\Resources\UserResource;
use App\Modules\Rbac\Http\Requests\AssignRolesRequest;
use App\Modules\Rbac\Services\RbacService;
use Illuminate\Http\JsonResponse;

class UserRoleController extends ApiController
{
    public function __construct(private RbacService $rbac)
    {
    }

    public function update(AssignRolesRequest $request, string $userId): JsonResponse
    {
        $target = User::query()->withoutGlobalScopes()->findOrFail($userId);
        $this->authorize('assignRoles', $target);

        $user = $this->rbac->syncUserRoles(
            $request->user(),
            $target,
            $request->validated('role_ids')
        );

        return $this->success(new UserResource($user->load('roles')), 'Papéis atualizados.');
    }
}
