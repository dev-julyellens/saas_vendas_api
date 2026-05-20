<?php

use App\Modules\Analytics\Http\Controllers\V1\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::prefix('analytics')->group(function ()
{
    Route::get('dashboard', [AnalyticsController::class, 'dashboard'])
        ->middleware('permission:sales.view');
});
