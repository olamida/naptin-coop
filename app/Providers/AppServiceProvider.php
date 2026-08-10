<?php

namespace App\Providers;

use App\Models\Loan;
use App\Policies\LoanPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Loan::class, LoanPolicy::class);

        RateLimiter::for('login', function (Request $request) {
            $key = strtolower($request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('global', function (Request $request) {
            $key = ($request->user()?->id ?? $request->ip());

            return Limit::perMinute(60)->by($key);
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $key = strtolower($request->input('email')).'|'.$request->ip();

            return Limit::perMinute(3)->by($key);
        });

        RateLimiter::for('uploads', function (Request $request) {
            $key = ($request->user()?->id ?? $request->ip());

            return Limit::perMinute(10)->by($key);
        });

        // Stricter budget for the ledger & finance surfaces — heavy double-entry
        // work and sensitive reporting should not be hammered by a single session.
        RateLimiter::for('finance', function (Request $request) {
            $key = ($request->user()?->id ?? $request->ip());

            return Limit::perMinute(30)->by($key);
        });
    }
}
