<?php

use App\Modules\Commission\Http\Controllers\V1\CommissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('commissions')->middleware('permission:commissions.manage')->group(function ()
{
    Route::get('/', [CommissionController::class, 'index']);
    Route::get('{commission}', [CommissionController::class, 'show']);
    Route::patch('{commission}/status', [CommissionController::class, 'updateStatus']);
});
