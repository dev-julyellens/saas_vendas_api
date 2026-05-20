<?php

Route::prefix('commissions')->group(function ()
{
    Route::get('/', fn() => response()->json(['data' => []]));
});
