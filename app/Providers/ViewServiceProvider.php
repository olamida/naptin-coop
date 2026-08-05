<?php

namespace App\Providers;

use App\Models\Cart;
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

    /**
     * Read-only cart badge count for an actor, without creating or pruning cart rows.
     */
    private function cartCount(string $actorType, ?int $userId, ?int $memberId = null): int
    {
        if (! $userId) {
            return 0;
        }

        $query = Cart::where('actor_type', $actorType)
            ->where('user_id', $userId)
            ->where('expires_at', '>', now());

        if ($memberId === null) {
            $query->whereNull('member_id');
        } else {
            $query->where('member_id', $memberId);
        }

        return $query->get()->sum(fn (Cart $cart) => count($cart->items));
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
            $cartCount = $this->cartCount('admin', $user?->id);

            $view->with(compact('unreadCount', 'recentNotifications', 'company', 'cartCount'));
        });

        View::composer(['components.portal-layout'], function ($view) {
            $user = Auth::user();
            $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
            $recentNotifications = $user ? $user->notifications()->latest()->take(5)->get() : collect();
            $cartCount = $user?->member_id ? $this->cartCount('member', $user->id, $user->member_id) : 0;
            $hasNewProducts = Product::where('enabled', true)
                ->where('created_at', '>=', now()->subDays(7))
                ->where('stock_quantity', '>', 0)
                ->exists();
            $company = Company::instance();

            $view->with(compact('unreadCount', 'recentNotifications', 'cartCount', 'hasNewProducts', 'company'));
        });
    }
}
