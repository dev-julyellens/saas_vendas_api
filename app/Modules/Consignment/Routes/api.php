<?php

use App\Modules\Consignment\Http\Controllers\V1\ConsignmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('consignments')->group(function ()
{
    Route::get('/', [ConsignmentController::class, 'index'])
        ->middleware('permission:consignment.view');

    Route::post('/', [ConsignmentController::class, 'store'])
        ->middleware('permission:consignment.manage');

    Route::get('{consignment}', [ConsignmentController::class, 'show'])
        ->middleware('permission:consignment.view');

    Route::post('{consignment}/dispatch', [ConsignmentController::class, 'dispatchShipment'])
        ->middleware('permission:consignment.manage');

    Route::post('{consignment}/partial-sale', [ConsignmentController::class, 'partialSale'])
        ->middleware('permission:consignment.manage');

    Route::post('{consignment}/partial-return', [ConsignmentController::class, 'partialReturn'])
        ->middleware('permission:consignment.manage');

    Route::post('{consignment}/loss', [ConsignmentController::class, 'loss'])
        ->middleware('permission:consignment.manage');

    Route::post('{consignment}/damage', [ConsignmentController::class, 'damage'])
        ->middleware('permission:consignment.manage');

    Route::post('{consignment}/divergence', [ConsignmentController::class, 'divergence'])
        ->middleware('permission:consignment.manage');

    Route::post('{consignment}/collect', [ConsignmentController::class, 'collect'])
        ->middleware('permission:consignment.manage');

    Route::post('{consignment}/close', [ConsignmentController::class, 'close'])
        ->middleware('permission:consignment.manage');

    Route::get('{consignment}/operations', [ConsignmentController::class, 'operations'])
        ->middleware('permission:consignment.view');

    Route::get('{consignment}/movements', [ConsignmentController::class, 'movements'])
        ->middleware('permission:consignment.view');
});
