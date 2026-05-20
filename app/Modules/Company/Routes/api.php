<?php

use Illuminate\Support\Facades\Route;

// Rotas de Company — expandir com CompanyController (CRUD tenant settings).
Route::get('company/profile', fn() => response()->json(['message' => 'Implementar CompanyController']));
