<?php

Route::prefix('customers')->group(function ()
{
    Route::get('/', fn() => response()->json(['data' => []]));
});
