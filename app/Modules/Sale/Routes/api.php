<?php

Route::prefix('sales')->group(function ()
{
    Route::get('/', fn() => response()->json(['data' => []]));
});
