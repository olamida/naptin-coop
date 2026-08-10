<?php

use App\Http\Middleware\EnforceSingleSession;
use App\Http\Middleware\MemberGuard;
use App\Http\Middleware\ModuleEnabled;
use App\Http\Middleware\MustChangePassword;
use App\Http\Middleware\NoBackDating;
use App\Http\Middleware\PortalMember;
use App\Http\Middleware\PreventCache;
use App\Http\Middleware\RequireTwoFactor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
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
            'module.enabled' => ModuleEnabled::class,
            'prevent-cache' => PreventCache::class,
            'no-back-dating' => NoBackDating::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A stale CSRF token on the login/logout forms (e.g. a cached page or an
        // expired session) should bounce back to a fresh login instead of a dead-end
        // "419 Page Expired" screen.
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->is('login', 'logout')) {
                return redirect()->route('login')->with('error', 'Your session expired. Please sign in again.');
            }

            return response()->view('errors.419', [], 419);
        });
    })->create();
