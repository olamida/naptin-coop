<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->totp_enabled && ! $request->session()->get('two_factor_verified', false)) {
            return redirect()->route('two-factor.challenge');
        }

        $forcedRoles = config('security.enforce_two_factor_roles', []);

        if (! empty($forcedRoles) && ! $user->totp_enabled && $user->hasAnyRole($forcedRoles)) {
            return redirect()->route('two-factor.setup')
                ->with('warning', 'Two-factor authentication is required for your role. Please enrol before continuing.');
        }

        return $next($request);
    }
}
