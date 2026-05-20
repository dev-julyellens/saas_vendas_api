<?php

use App\Modules\Sale\Http\Controllers\V1\SaleController;
use Illuminate\Support\Facades\Route;

Route::prefix('sales')->group(function ()
{
    Route::get('/dashboard', [SaleController::class, 'dashboard'])
        ->middleware('permission:sales.view');

    Route::get('/report', [SaleController::class, 'report'])
        ->middleware('permission:sales.view');

    Route::get('/', [SaleController::class, 'index'])
        ->middleware('permission:sales.view');

    Route::post('/', [SaleController::class, 'store'])
        ->middleware('permission:sales.manage');

    Route::get('{sale}', [SaleController::class, 'show'])
        ->middleware('permission:sales.view');

    Route::put('{sale}', [SaleController::class, 'update'])
        ->middleware('permission:sales.manage');

    Route::delete('{sale}', [SaleController::class, 'destroy'])
        ->middleware('permission:sales.manage');

    Route::post('{sale}/confirm', [SaleController::class, 'confirm'])
        ->middleware('permission:sales.manage');

    Route::post('{sale}/cancel', [SaleController::class, 'cancel'])
        ->middleware('permission:sales.manage');
});
