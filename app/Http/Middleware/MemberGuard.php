<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->member_id && ! $user->hasAnyRole(['super-admin', 'admin', 'secretary', 'treasurer', 'loan-officer', 'teller'])) {
            return redirect()->route('portal.dashboard');
        }

        return $next($request);
    }
}
