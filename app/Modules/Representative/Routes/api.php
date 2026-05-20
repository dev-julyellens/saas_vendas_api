<?php

Route::prefix('representatives')->group(function ()
{
    Route::get('/', fn() => response()->json(['data' => []]));
});
