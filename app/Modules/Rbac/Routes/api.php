<?php

use App\Modules\Rbac\Http\Controllers\V1\PermissionController;
use App\Modules\Rbac\Http\Controllers\V1\RoleController;
use App\Modules\Rbac\Http\Controllers\V1\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('rbac')->middleware('permission:roles.manage')->group(function ()
{
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('roles/{id}', [RoleController::class, 'show']);
});

Route::put('users/{userId}/roles', [UserRoleController::class, 'update'])
    ->middleware('permission:users.manage');
