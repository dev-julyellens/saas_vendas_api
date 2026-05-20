<?php

use App\Modules\Auth\Http\Controllers\V1\AuthController;
use App\Modules\Auth\Http\Controllers\V1\SessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function ()
{
  Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:auth-login');

  Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:auth-password');

  Route::post('reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:auth-password');

  Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('auth.verification.verify');

  Route::middleware(['auth.api'])->group(function ()
  {
    Route::post('logout', [AuthController::class, 'logout'])
      ->middleware('throttle:auth-refresh');

    Route::post('refresh', [AuthController::class, 'refresh'])
      ->middleware('throttle:auth-refresh');

    Route::get('me', [AuthController::class, 'me'])
      ->middleware(['tenant', 'tenant.company']);

    Route::post('email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
      ->middleware('throttle:auth-password');

    Route::prefix('sessions')->group(function ()
    {
      Route::get('/', [SessionController::class, 'index']);
      Route::delete('{sessionId}', [SessionController::class, 'destroy']);
      Route::delete('/', [SessionController::class, 'destroyAll']);
    });
  });
});
