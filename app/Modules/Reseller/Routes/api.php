<?php

use App\Modules\Reseller\Http\Controllers\V1\ResellerController;
use Illuminate\Support\Facades\Route;

Route::prefix('resellers')->middleware('permission:resellers.manage')->group(function ()
{
    Route::get('/', [ResellerController::class, 'index']);
    Route::post('/', [ResellerController::class, 'store']);
    Route::get('{reseller}', [ResellerController::class, 'show']);
    Route::put('{reseller}', [ResellerController::class, 'update']);
    Route::delete('{reseller}', [ResellerController::class, 'destroy']);
});
