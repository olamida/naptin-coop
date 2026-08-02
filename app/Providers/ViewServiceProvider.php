<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Product;
use App\Services\BrandingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $branding = app(BrandingService::class);

        View::composer(['components.app-layout', 'components.public-layout', 'components.portal-layout'], function ($view) use ($branding) {
            $company = Company::instance();
            $view->with(compact('company', 'branding'));
        });

        View::composer(['components.app-layout'], function ($view) {
            $user = Auth::user();
            $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
            $recentNotifications = $user ? $user->notifications()->latest()->take(8)->get() : collect();
            $company = Company::instance();
            $cartCount = count(session('cart', []));

            $view->with(compact('unreadCount', 'recentNotifications', 'company', 'cartCount'));
        });

        View::composer(['components.portal-layout'], function ($view) {
            $user = Auth::user();
            $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
            $recentNotifications = $user ? $user->notifications()->latest()->take(5)->get() : collect();
            $cartCount = count(session('cart', []));
            $hasNewProducts = Product::where('enabled', true)
                ->where('created_at', '>=', now()->subDays(7))
                ->where('stock_quantity', '>', 0)
                ->exists();
            $company = Company::instance();

            $view->with(compact('unreadCount', 'recentNotifications', 'cartCount', 'hasNewProducts', 'company'));
        });
    }
}
