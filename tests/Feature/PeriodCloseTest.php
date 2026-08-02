<?php

namespace Tests\Feature;

use App\Models\PeriodClose;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PeriodCloseTest extends TestCase
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

    public function test_admin_can_close_current_period(): void
    {
        [$admin, $token] = $this->admin();
        $period = now()->format('Y-m');

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('finance.period-close.store'), [
                'period' => $period,
                'notes' => 'Month end',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(PeriodClose::isClosed($period));
        $this->assertDatabaseHas('activity_logs', ['event' => 'period.close']);
    }

    public function test_admin_can_reopen_period_with_reason(): void
    {
        [$admin, $token] = $this->admin();
        $period = now()->format('Y-m');

        PeriodClose::create([
            'period' => $period,
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => $admin->id,
        ]);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('finance.period-close.reopen', $period), [
                'reason' => 'Adjustment required',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse(PeriodClose::isClosed($period));
        $this->assertDatabaseHas('activity_logs', ['event' => 'period.reopen']);
    }

    public function test_reopen_requires_reason(): void
    {
        [$admin, $token] = $this->admin();
        $period = now()->format('Y-m');

        PeriodClose::create([
            'period' => $period,
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => $admin->id,
        ]);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('finance.period-close.reopen', $period), [])
            ->assertSessionHasErrors('reason');

        $this->assertTrue(PeriodClose::isClosed($period));
    }

    public function test_guest_cannot_access_finance_routes(): void
    {
        $this->get(route('finance.index'))->assertRedirect(route('login'));
    }
}
