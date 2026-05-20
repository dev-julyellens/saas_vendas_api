<?php

use App\Modules\Product\Http\Controllers\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class)
    ->middleware('permission:products.manage');
