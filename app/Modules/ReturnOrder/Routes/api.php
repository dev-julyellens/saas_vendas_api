<?php

Route::prefix('returns')->group(function ()
{
    Route::get('/', fn() => response()->json(['data' => []]));
});
