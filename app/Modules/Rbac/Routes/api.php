<?php

// RBAC: roles, permissions — implementar controllers em iteração futura.
Route::prefix('rbac')->group(function ()
{
    Route::get('permissions', fn() => response()->json(['data' => []]));
});
