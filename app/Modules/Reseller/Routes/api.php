<?php

Route::prefix('resellers')->group(function ()
{
    Route::get('/', fn() => response()->json(['data' => []]));
});
