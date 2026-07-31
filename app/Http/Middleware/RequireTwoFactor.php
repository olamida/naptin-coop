<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->totp_enabled && !$request->session()->get('two_factor_verified', false)) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
