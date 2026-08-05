<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! Company::instance()->moduleEnabled($module)) {
            $url = $request->user()?->member_id ? route('portal.dashboard') : route('dashboard');

            return redirect($url)->with('error', 'That module is currently disabled.');
        }

        return $next($request);
    }
}
