<?php

Route::prefix('consignment-stocks')->group(function ()
{
    Route::get('/', fn() => response()->json(['data' => []]));
});
