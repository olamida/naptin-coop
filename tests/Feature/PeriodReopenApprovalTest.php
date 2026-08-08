<?php

namespace Tests\Feature;

use App\Models\ApprovalWorkflow;
use App\Models\PendingApproval;
use App\Models\PeriodClose;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PeriodReopenApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ApprovalWorkflow::create([
            'key' => 'period_reopen',
            'name' => 'Period Reopen',
            'required_permission' => 'manage-users',
            'required_roles' => ['president', 'auditor'],
            'threshold_amount' => null,
            'enabled' => true,
        ]);
    }

    private function makeAdmin(): array
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'super-admin']));
        Permission::firstOrCreate(['name' => 'manage-users']);
        $admin->givePermissionTo('manage-users');
        $token = 'test-session-'.uniqid();
        $admin->forceFill(['active_session_token' => $token])->save();

        return [$admin, $token];
    }

    private function actingPair(array $pair): static
    {
        [$user, $token] = $pair;

        return $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($user);
    }

    private function closePeriod(array $pair): void
    {
        $this->actingPair($pair)
            ->post(route('finance.period-close.store'), ['period' => now()->format('Y-m')])
            ->assertSessionHas('success');
    }

    public function test_reopen_requires_reason(): void
    {
        $pair = $this->makeAdmin();
        $this->closePeriod($pair);

        $this->actingPair($pair)
            ->post(route('finance.period-close.reopen', now()->format('Y-m')), ['reason' => ''])
            ->assertSessionHasErrors('reason');
    }

    public function test_reopen_request_creates_two_pending_approvals_and_keeps_period_closed(): void
    {
        $pair = $this->makeAdmin();
        [$admin] = $pair;
        $this->closePeriod($pair);

        $this->actingPair($pair)
            ->post(route('finance.period-close.reopen', now()->format('Y-m')), ['reason' => 'Correct a posting error'])
            ->assertSessionHas('success');

        $close = PeriodClose::where('period', now()->format('Y-m'))->first();
        $this->assertTrue($close->is_closed);

        $slots = PendingApproval::where('workflow', 'period_reopen')
            ->where('approvable_type', PeriodClose::class)
            ->where('approvable_id', $close->id)
            ->get();

        $this->assertCount(2, $slots);
        $this->assertEqualsCanonicalizing(['president', 'auditor'], $slots->pluck('required_role')->all());
        $this->assertEquals([PendingApproval::STATUS_PENDING, PendingApproval::STATUS_PENDING], $slots->pluck('status')->sort()->values()->all());
        $this->assertEquals($admin->id, $slots->first()->requested_by);
        $this->assertEquals('Correct a posting error', $slots->first()->reason);
    }

    public function test_duplicate_reopen_request_is_blocked(): void
    {
        $pair = $this->makeAdmin();
        $this->closePeriod($pair);

        $this->actingPair($pair)
            ->post(route('finance.period-close.reopen', now()->format('Y-m')), ['reason' => 'First reason'])
            ->assertSessionHas('success');

        $this->actingPair($pair)
            ->post(route('finance.period-close.reopen', now()->format('Y-m')), ['reason' => 'Second reason'])
            ->assertSessionHasErrors('error');
    }

    public function test_single_approval_does_not_reopen_period(): void
    {
        $pair = $this->makeAdmin();
        $this->closePeriod($pair);

        $this->actingPair($pair)
            ->post(route('finance.period-close.reopen', now()->format('Y-m')), ['reason' => 'Fix data error'])
            ->assertSessionHas('success');

        [$approverA, $tokenA] = $this->makeAdmin();
        $this->actingPair([$approverA, $tokenA])
            ->post(route('finance.period-close.reopen.approve', now()->format('Y-m')))
            ->assertSessionHas('success');

        $close = PeriodClose::where('period', now()->format('Y-m'))->first();
        $this->assertTrue($close->is_closed);
        $this->assertEquals(1, PendingApproval::where('status', PendingApproval::STATUS_PENDING)->count());
    }

    public function test_requester_cannot_approve_own_request(): void
    {
        $pair = $this->makeAdmin();
        $this->closePeriod($pair);

        $this->actingPair($pair)
            ->post(route('finance.period-close.reopen', now()->format('Y-m')), ['reason' => 'Fix data error'])
            ->assertSessionHas('success');

        $this->actingPair($pair)
            ->post(route('finance.period-close.reopen.approve', now()->format('Y-m')))
            ->assertSessionHasErrors('error');

        $this->assertEquals(2, PendingApproval::where('status', PendingApproval::STATUS_PENDING)->count());
    }

    public function test_same_approver_cannot_fill_both_slots(): void
    {
        $pair = $this->makeAdmin();
        $this->closePeriod($pair);

        $this->actingPair($pair)
            ->post(route('finance.period-close.reopen', now()->format('Y-m')), ['reason' => 'Fix data error'])
            ->assertSessionHas('success');

        [$approver, $token] = $this->makeAdmin();
        $this->actingPair([$approver, $token])
            ->post(route('finance.period-close.reopen.approve', now()->format('Y-m')))
            ->assertSessionHas('success');

        $this->actingPair([$approver, $token])
            ->post(route('finance.period-close.reopen.approve', now()->format('Y-m')))
            ->assertSessionHasErrors('error');
    }

    public function test_dual_approval_by_two_distinct_users_reopens_period(): void
    {
        $pair = $this->makeAdmin();
        $this->closePeriod($pair);

        $this->actingPair($pair)
            ->post(route('finance.period-close.reopen', now()->format('Y-m')), ['reason' => 'Adjust opening balances'])
            ->assertSessionHas('success');

        [$approverA, $tokenA] = $this->makeAdmin();
        [$approverB, $tokenB] = $this->makeAdmin();

        $this->actingPair([$approverA, $tokenA])
            ->post(route('finance.period-close.reopen.approve', now()->format('Y-m')))
            ->assertSessionHas('success');

        $this->actingPair([$approverB, $tokenB])
            ->post(route('finance.period-close.reopen.approve', now()->format('Y-m')))
            ->assertSessionHas('success');

        $close = PeriodClose::where('period', now()->format('Y-m'))->first();
        $this->assertFalse($close->is_closed);
        $this->assertNotNull($close->reopened_at);
        $this->assertEquals($approverB->id, $close->reopened_by);
        $this->assertEquals('Adjust opening balances', $close->reopen_reason);
        $this->assertEquals(0, PendingApproval::where('status', PendingApproval::STATUS_PENDING)->count());
    }
}
