<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): array
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'super-admin']));
        Permission::firstOrCreate(['name' => 'manage-users']);
        $admin->givePermissionTo('manage-users');
        $token = 'test-session-'.uniqid();
        $admin->forceFill(['active_session_token' => $token])->save();

        return [$admin, $token];
    }

    public function test_ledger_and_finance_routes_carry_the_finance_throttle_and_no_back_dating(): void
    {
        foreach (['finance.cash-count.store', 'ledger.journals.store'] as $name) {
            $middleware = Route::getRoutes()->getByName($name)->gatherMiddleware();

            $this->assertContains('throttle:finance', $middleware, "{$name} should be throttled");
            $this->assertContains('no-back-dating', $middleware, "{$name} should guard against back-dating");
        }
    }

    public function test_finance_routes_are_throttled_per_user(): void
    {
        RateLimiter::for('finance', fn () => Limit::perMinute(1));

        [$admin, $token] = $this->admin();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->get(route('finance.index'))
            ->assertOk();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->get(route('finance.index'))
            ->assertTooManyRequests();
    }
}
