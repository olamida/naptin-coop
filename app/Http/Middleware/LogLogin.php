<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($request->session()->has('login_activity_logged')) {
            return;
        }

        if (auth()->check() && $request->isMethod('post') && $request->routeIs('login') === false) {
            return;
        }

        if (auth()->check() && $request->session()->previousUrl() === '' && $request->is('login')) {
            $request->session()->put('login_activity_logged', true);
        }
    }
}
