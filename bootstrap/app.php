<?php

use App\Http\Middleware\EnforceSingleSession;
use App\Http\Middleware\MemberGuard;
use App\Http\Middleware\MustChangePassword;
use App\Http\Middleware\PortalMember;
use App\Http\Middleware\RequireTwoFactor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'portal-member' => PortalMember::class,
            'admin-only' => MemberGuard::class,
            'enforce-single-session' => EnforceSingleSession::class,
            'must-change-password' => MustChangePassword::class,
            'two-factor' => RequireTwoFactor::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
