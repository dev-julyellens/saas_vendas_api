<?php

use Illuminate\Support\Facades\Route;

// Rotas globais — módulos registram endpoints em app/Modules/{Module}/Routes/api.php
Route::get('/', function ()
{
    return response()->json([
        'name' => config('app.name'),
        'version' => 'v1',
        'status' => 'ok',
    ]);
});
