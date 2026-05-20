<?php

Route::prefix('financial')->group(function ()
{
    Route::get('/transactions', fn() => response()->json(['data' => []]));
});
