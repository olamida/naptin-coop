<?php

namespace Tests\Feature;

use App\Models\ApprovalWorkflow;
use App\Models\Loan;
use App\Models\Member;
use App\Models\PendingApproval;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MakerCheckerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ApprovalWorkflow::create([
            'key' => 'loan_disbursement',
            'name' => 'Loan Disbursement',
            'required_permission' => 'disburse-loans',
            'required_roles' => ['treasurer', 'auditor'],
            'threshold_amount' => null,
            'enabled' => true,
        ]);

        ApprovalWorkflow::create([
            'key' => 'savings_withdrawal',
            'name' => 'High-Value Savings Withdrawal',
            'required_permission' => 'withdraw-savings',
            'required_roles' => ['treasurer'],
            'threshold_amount' => 100000.00,
            'enabled' => true,
        ]);
    }

    private function makeUser(): array
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'treasurer']));
        Permission::firstOrCreate(['name' => 'disburse-loans']);
        $user->givePermissionTo('disburse-loans');
        $token = 'test-session-'.uniqid();
        $user->forceFill(['active_session_token' => $token])->save();

        return [$user, $token];
    }

    private function actingPair(array $pair): static
    {
        [$user, $token] = $pair;

        return $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($user);
    }

    private function makeApprovedLoan(): Loan
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'STAFF-'.substr(uniqid(), -6),
            'first_name' => 'Test',
            'last_name' => 'Member',
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);

        return Loan::create([
            'member_id' => $member->id,
            'loan_number' => 'LN-'.substr(uniqid(), -6),
            'type' => 'regular',
            'amount' => 50000,
            'interest_rate' => 5,
            'tenure_months' => 12,
            'monthly_repayment' => 4375,
            'outstanding' => 50000,
            'processing_fee' => 0,
            'application_date' => now()->toDateString(),
            'status' => 'approved',
        ]);
    }

    public function test_disburse_creates_maker_checker_request_and_keeps_loan_approved(): void
    {
        $pair = $this->makeUser();
        [$maker] = $pair;
        $loan = $this->makeApprovedLoan();

        $this->actingPair($pair)
            ->post(route('loans.disburse', $loan))
            ->assertSessionHas('success');

        $this->assertSame('approved', $loan->fresh()->status);

        $slots = PendingApproval::where('workflow', 'loan_disbursement')
            ->where('approvable_type', Loan::class)
            ->where('approvable_id', $loan->id)
            ->get();

        $this->assertCount(2, $slots);
        $this->assertEquals($maker->id, $slots->first()->requested_by);
    }

    public function test_disburse_blocked_while_approval_pending(): void
    {
        $pair = $this->makeUser();
        $loan = $this->makeApprovedLoan();

        $this->actingPair($pair)
            ->post(route('loans.disburse', $loan))
            ->assertSessionHas('success');

        [$other, $token] = $this->makeUser();
        $this->actingPair([$other, $token])
            ->post(route('loans.disburse', $loan))
            ->assertSessionHasErrors('error');

        $this->assertSame('approved', $loan->fresh()->status);
    }

    public function test_maker_cannot_approve_own_disbursement(): void
    {
        $pair = $this->makeUser();
        $loan = $this->makeApprovedLoan();

        $this->actingPair($pair)
            ->post(route('loans.disburse', $loan))
            ->assertSessionHas('success');

        $this->actingPair($pair)
            ->post(route('loans.disburse.approve', $loan))
            ->assertSessionHasErrors('error');

        $this->assertSame('approved', $loan->fresh()->status);
    }

    public function test_single_checker_approval_is_not_enough(): void
    {
        $pair = $this->makeUser();
        $loan = $this->makeApprovedLoan();

        $this->actingPair($pair)
            ->post(route('loans.disburse', $loan))
            ->assertSessionHas('success');

        [$checkerA, $tokenA] = $this->makeUser();
        $this->actingPair([$checkerA, $tokenA])
            ->post(route('loans.disburse.approve', $loan))
            ->assertSessionHas('success');

        $this->assertSame('approved', $loan->fresh()->status);
        $this->assertEquals(1, PendingApproval::where('status', PendingApproval::STATUS_PENDING)->count());
    }

    public function test_dual_approval_disburses_loan(): void
    {
        $pair = $this->makeUser();
        $loan = $this->makeApprovedLoan();

        $this->actingPair($pair)
            ->post(route('loans.disburse', $loan))
            ->assertSessionHas('success');

        [$checkerA, $tokenA] = $this->makeUser();
        [$checkerB, $tokenB] = $this->makeUser();

        $this->actingPair([$checkerA, $tokenA])
            ->post(route('loans.disburse.approve', $loan))
            ->assertSessionHas('success');

        $this->assertSame('approved', $loan->fresh()->status);

        $this->actingPair([$checkerB, $tokenB])
            ->post(route('loans.disburse.approve', $loan))
            ->assertSessionHas('success');

        $loan->refresh();
        $this->assertSame('disbursed', $loan->status);
        $this->assertNotNull($loan->disbursement_date);
    }

    public function test_high_value_withdrawal_requires_maker_checker(): void
    {
        $member = $this->makeMemberWithSavings();
        $maker = $this->makeSavingsUser();
        [$makerUser, $makerToken] = $maker;

        $this->actingPair($maker)
            ->post(route('savings.withdraw.store'), ['member_id' => $member->id, 'amount' => 150000])
            ->assertSessionHas('success');

        $txn = $member->savingsAccount->transactions()->where('type', 'withdrawal')->first();
        $this->assertSame('pending', $txn->status);
        $this->assertEquals(1, PendingApproval::where('workflow', 'savings_withdrawal')->count());
        $this->assertEquals($makerUser->id, $txn->requested_by);

        [$checker, $token] = $this->makeSavingsUser();
        $this->actingPair([$checker, $token])
            ->post(route('savings.withdrawals.approve', $txn))
            ->assertSessionHas('success');

        $this->assertSame('completed', $txn->fresh()->status);
    }

    public function test_low_value_withdrawal_bypasses_maker_checker(): void
    {
        $member = $this->makeMemberWithSavings();
        $maker = $this->makeSavingsUser();

        $this->actingPair($maker)
            ->post(route('savings.withdraw.store'), ['member_id' => $member->id, 'amount' => 50000])
            ->assertSessionHas('success');

        $txn = $member->savingsAccount->transactions()->where('type', 'withdrawal')->first();
        $this->assertSame('pending', $txn->status);
        $this->assertEquals(0, PendingApproval::where('workflow', 'savings_withdrawal')->count());

        [$checker, $token] = $this->makeSavingsUser();
        $this->actingPair([$checker, $token])
            ->post(route('savings.withdrawals.approve', $txn))
            ->assertSessionHas('success');

        $this->assertSame('completed', $txn->fresh()->status);
    }

    private function makeMemberWithSavings(): Member
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'STAFF-'.substr(uniqid(), -6),
            'first_name' => 'Test',
            'last_name' => 'Member',
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);

        $member->savingsAccount()->create([
            'account_number' => 'SAV-'.substr(uniqid(), -8),
            'balance' => 300000,
            'status' => 'active',
        ]);

        return $member;
    }

    private function makeSavingsUser(): array
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'treasurer']));
        Permission::firstOrCreate(['name' => 'withdraw-savings']);
        $user->givePermissionTo('withdraw-savings');
        $token = 'test-session-'.uniqid();
        $user->forceFill(['active_session_token' => $token])->save();

        return [$user, $token];
    }
}
