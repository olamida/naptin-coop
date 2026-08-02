<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Region;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\ProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProvisioningTest extends TestCase
{
    use RefreshDatabase;

    private function member(): Member
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        return Member::create([
            'region_id' => $region->id,
            'staff_id' => 'STAFF'.substr(uniqid(), -6),
            'first_name' => 'Test',
            'last_name' => 'Member',
            'email' => null,
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);
    }

    private function loan(Member $member, float $amount, ?float $outstanding = null, string $status = 'repaying'): Loan
    {
        return Loan::create([
            'member_id' => $member->id,
            'loan_number' => 'LN-'.substr(uniqid(), -8),
            'type' => 'regular',
            'amount' => $amount,
            'interest_rate' => 5,
            'tenure_months' => 12,
            'monthly_repayment' => round($amount * 1.05 / 12, 2),
            'outstanding' => $outstanding ?? $amount,
            'application_date' => now()->subMonths(2)->toDateString(),
            'disbursement_date' => now()->subMonths(1)->toDateString(),
            'status' => $status,
        ]);
    }

    public function test_classification_buckets_match_cbn_guidance(): void
    {
        $this->assertSame(['classification' => 'Performing', 'rate' => 0.01], ProvisioningService::classify(0));
        $this->assertSame(['classification' => 'Performing', 'rate' => 0.01], ProvisioningService::classify(30));
        $this->assertSame(['classification' => 'Pass & Watch', 'rate' => 0.25], ProvisioningService::classify(45));
        $this->assertSame(['classification' => 'Substandard', 'rate' => 0.50], ProvisioningService::classify(75));
        $this->assertSame(['classification' => 'Doubtful', 'rate' => 0.75], ProvisioningService::classify(150));
        $this->assertSame(['classification' => 'Lost', 'rate' => 1.00], ProvisioningService::classify(200));
    }

    public function test_aging_report_returns_expected_structure(): void
    {
        $member = $this->member();
        $this->loan($member, 100000);

        $report = ProvisioningService::agingReport();

        $this->assertSame(now()->format('Y-m'), $report['period']);
        $this->assertCount(1, $report['rows']);
        $this->assertEquals(100000, $report['total_outstanding']);
        $this->assertEquals(1000, $report['total_provision']);
        $this->assertEquals(1.0, $report['coverage_ratio']);
        $this->assertSame('Performing', $report['rows'][0]['classification']);
    }

    public function test_calculate_posts_provision_and_is_idempotent(): void
    {
        $member = $this->member();
        $this->loan($member, 100000);

        $first = ProvisioningService::calculate();

        $this->assertEquals(1000, $first['required_provision']);
        $this->assertEquals(1000, $first['delta']);
        $this->assertDatabaseCount('journal_entries', 1);
        $this->assertDatabaseCount('loan_loss_provisions', 1);

        $provisionAccount = ChartOfAccount::where('code', LedgerService::LOAN_LOSS_PROVISION)->first();
        $this->assertEquals(1000, $provisionAccount->journalLines()->sum('credit') - $provisionAccount->journalLines()->sum('debit'));

        $second = ProvisioningService::calculate();

        $this->assertEquals(0, $second['delta']);
        $this->assertDatabaseCount('journal_entries', 1);
        $this->assertDatabaseCount('loan_loss_provisions', 1);
    }

    public function test_aging_report_marks_defaulted_loan_as_lost(): void
    {
        $member = $this->member();
        $loan = $this->loan($member, 100000);
        $loan->update([
            'status' => 'defaulted',
            'maturity_date' => now()->subDays(200)->toDateString(),
        ]);

        $report = ProvisioningService::agingReport();

        $this->assertSame('Lost', $report['rows'][0]['classification']);
        $this->assertEquals(100000, $report['total_provision']);
    }

    public function test_finance_statement_views_render_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'super-admin']));
        Permission::firstOrCreate(['name' => 'manage-users']);
        $user->givePermissionTo('manage-users');
        $token = 'test-session-'.uniqid();
        $user->forceFill(['active_session_token' => $token])->save();

        $member = $this->member();
        $this->loan($member, 100000);
        ProvisioningService::calculate();

        $this->withSession(['active_session_token' => $token])->actingAs($user)
            ->get(route('finance.index'))->assertOk();
        $this->withSession(['active_session_token' => $token])->actingAs($user)
            ->get(route('finance.profit-loss'))->assertOk();
        $this->withSession(['active_session_token' => $token])->actingAs($user)
            ->get(route('finance.balance-sheet'))->assertOk();
        $this->withSession(['active_session_token' => $token])->actingAs($user)
            ->get(route('finance.cash-flow'))->assertOk();
        $this->withSession(['active_session_token' => $token])->actingAs($user)
            ->get(route('finance.loan-aging'))->assertOk();
        $this->withSession(['active_session_token' => $token])->actingAs($user)
            ->get(route('finance.control-reconciliation'))->assertOk();
        $this->withSession(['active_session_token' => $token])->actingAs($user)
            ->get(route('finance.audit-trail'))->assertOk();
        $this->withSession(['active_session_token' => $token])->actingAs($user)
            ->get(route('finance.period-close'))->assertOk();
    }
}
