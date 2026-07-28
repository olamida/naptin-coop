<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnforceSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $sessionToken = session('active_session_token');

        if (!$sessionToken || $sessionToken !== $user->active_session_token) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your account has been logged in from another location. Please log in again.');
        }

        return $next($request);
    }
}
