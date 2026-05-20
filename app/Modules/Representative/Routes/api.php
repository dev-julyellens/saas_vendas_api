<?php

use App\Modules\Representative\Http\Controllers\V1\RepresentativeController;
use Illuminate\Support\Facades\Route;

Route::prefix('representatives')->group(function ()
{
    Route::get('/', [RepresentativeController::class, 'index'])
        ->middleware('permission:representatives.manage');
});
