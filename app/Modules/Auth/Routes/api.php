<?php

use App\Modules\Auth\Http\Controllers\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function ()
{
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:auth-login');

    Route::middleware(['auth:api'])->group(function ()
    {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me'])
            ->middleware(['tenant', 'tenant.company']);
    });
});
