<?php

use App\Core\Exceptions\ApiExceptionHandler;
use App\Core\Http\Middleware\CheckPermission;
use App\Core\Http\Middleware\CheckRole;
use App\Core\Http\Middleware\EnsureEmailIsVerified;
use App\Core\Http\Middleware\EnsureSuperAdmin;
use App\Core\Http\Middleware\EnsureUserHasCompany;
use App\Core\Http\Middleware\EnsureUserIsActive;
use App\Core\Http\Middleware\ForceJsonResponse;
use App\Core\Http\Middleware\SetTenantFromAuthenticatedUser;
use App\Core\Http\Middleware\ValidateJwtSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

/**
 * Bootstrap API ONLY — sem rotas web/Blade.
 * Versionamento: prefixo /api/v1 registrado nos ModuleServiceProviders.
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware)
    {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'tenant' => SetTenantFromAuthenticatedUser::class,
            'tenant.company' => EnsureUserHasCompany::class,
            'permission' => CheckPermission::class,
            'role' => CheckRole::class,
            'super-admin' => EnsureSuperAdmin::class,
        ]);

        $middleware->group('auth.api', [
            'auth:api',
            ValidateJwtSession::class,
            EnsureUserIsActive::class,
            EnsureEmailIsVerified::class,
        ]);

        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions)
    {
        $exceptions->render(function (\Throwable $e, Request $request)
        {
            return ApiExceptionHandler::render($e, $request);
        });
    })->create();
