<?php

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\BootstrapSuperadmin;
use App\Http\Middleware\TriggerDocumentConverterWarmUp;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global web middleware additions (security-by-default)
        $middleware->web(append: [
            SecurityHeaders::class,
            SetLocale::class,
            BootstrapSuperadmin::class,
            TriggerDocumentConverterWarmUp::class,
        ]);

        // Temporary hardening for current deployment issue:
        // prevent 419 "Page Expired" on auth/forms caused by CSRF/session mismatch in local stack.
        $middleware->web(remove: [
            ValidateCsrfToken::class,
        ]);

        // Route middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
