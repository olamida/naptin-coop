<?php

namespace Tests\Feature;

use App\Actions\Loans\ApproveLoan;
use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\DisburseLoan;
use App\Models\LoanApprovalLog;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\Region;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanLifecycleTimelineTest extends TestCase
{
    use RefreshDatabase;

    private function makeMember(string $staffId): Member
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        return Member::create([
            'region_id' => $region->id,
            'staff_id' => $staffId,
            'first_name' => 'Test',
            'last_name' => 'Member',
            'monthly_salary' => 250000,
            'status' => 'active',
        ])->savingsAccount()->create([
            'account_number' => 'SAV-'.$staffId,
            'balance' => 2000000,
            'status' => 'active',
        ])->member;
    }

    private function makeProduct(): LoanProduct
    {
        return LoanProduct::create([
            'name' => 'Regular Loan',
            'slug' => 'regular-'.substr(uniqid(), -6),
            'min_amount' => 1000,
            'max_amount' => 2000000,
            'interest_rate' => 5,
            'processing_fee_pct' => 0,
            'repayment_method' => 'monthly',
            'max_term_months' => 12,
            'max_loans_per_member' => 3,
            'max_total_amount_per_member' => 4000000,
            'requires_guarantors' => false,
            'requires_collateral' => false,
            'enabled' => true,
        ]);
    }

    public function test_lifecycle_timeline_contains_all_major_events_in_order(): void
    {
        $member = $this->makeMember('STAFF-TL-'.substr(uniqid(), -6));
        $officer = User::create([
            'name' => 'Loan Officer',
            'email' => 'lo-'.substr(uniqid(), -6).'@naptin.coop',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($officer);

        $loan = CreateLoan::run([
            'member_id' => $member->id,
            'loan_product_id' => $this->makeProduct()->id,
            'type' => 'regular',
            'amount' => 100000,
            'interest_rate' => 5,
            'tenure_months' => 12,
        ]);
        app(LoanService::class)->generateRepaymentSchedules($loan);

        ApproveLoan::run($loan->fresh());
        DisburseLoan::run($loan->fresh());

        $timeline = $loan->fresh()->load('approvalLogs.user')->lifecycleTimeline();
        $titles = array_column($timeline, 'title');

        $this->assertContains('Application submitted', $titles);
        $this->assertContains('Loan approved', $titles);
        $this->assertContains('Loan disbursed', $titles);
        $this->assertContains('Repayment in progress', $titles);

        // Application is always the first event; disbursement comes before repayment progress.
        $this->assertEquals('Application submitted', $titles[0]);
        $this->assertLessThan(
            array_search('Repayment in progress', $titles),
            array_search('Loan disbursed', $titles)
        );

        // Every event carries avatar/actor metadata.
        foreach ($timeline as $event) {
            $this->assertArrayHasKey('title', $event);
            $this->assertArrayHasKey('date', $event);
            $this->assertArrayHasKey('actor_name', $event);
            $this->assertArrayHasKey('icon', $event);
            $this->assertArrayHasKey('color', $event);
        }
    }

    public function test_repayment_in_progress_event_reports_schedule_progress(): void
    {
        $member = $this->makeMember('STAFF-TL2-'.substr(uniqid(), -6));
        $this->actingAs(User::create([
            'name' => 'Loan Officer',
            'email' => 'lo-'.substr(uniqid(), -6).'@naptin.coop',
            'password' => bcrypt('password'),
        ]));

        $loan = CreateLoan::run([
            'member_id' => $member->id,
            'loan_product_id' => $this->makeProduct()->id,
            'type' => 'regular',
            'amount' => 100000,
            'interest_rate' => 5,
            'tenure_months' => 12,
        ]);
        app(LoanService::class)->generateRepaymentSchedules($loan);
        ApproveLoan::run($loan->fresh());
        DisburseLoan::run($loan->fresh());

        $timeline = $loan->fresh()->load('approvalLogs.user')->lifecycleTimeline();
        $progressEvent = collect($timeline)->firstWhere('title', 'Repayment in progress');

        $this->assertNotNull($progressEvent);
        $this->assertArrayHasKey('progress', $progressEvent);
        $this->assertEquals(0, $progressEvent['progress']['paid']);
        $this->assertEquals(12, $progressEvent['progress']['total']);
        $this->assertEquals(0.0, $progressEvent['progress']['percent']);
        $this->assertNotNull($progressEvent['progress']['next_due']);
    }

    public function test_rejected_loan_timeline_contains_rejection_event(): void
    {
        $member = $this->makeMember('STAFF-TL3-'.substr(uniqid(), -6));
        $officer = User::create([
            'name' => 'Loan Officer',
            'email' => 'lo-'.substr(uniqid(), -6).'@naptin.coop',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($officer);

        $loan = CreateLoan::run([
            'member_id' => $member->id,
            'loan_product_id' => $this->makeProduct()->id,
            'type' => 'regular',
            'amount' => 100000,
            'interest_rate' => 5,
            'tenure_months' => 12,
        ]);

        LoanApprovalLog::record($loan->id, 'rejected', 'pending', 'rejected', 'Insufficient savings history');
        $loan->update(['status' => 'rejected', 'rejection_reason' => 'Insufficient savings history']);

        $timeline = $loan->fresh()->load('approvalLogs.user')->lifecycleTimeline();
        $titles = array_column($timeline, 'title');

        $this->assertContains('Loan rejected', $titles);
        $this->assertNotContains('Loan disbursed', $titles);
        $this->assertNotContains('Repayment in progress', $titles);
    }

    public function test_completed_loan_timeline_contains_completion_event(): void
    {
        $member = $this->makeMember('STAFF-TL4-'.substr(uniqid(), -6));
        $this->actingAs(User::create([
            'name' => 'Loan Officer',
            'email' => 'lo-'.substr(uniqid(), -6).'@naptin.coop',
            'password' => bcrypt('password'),
        ]));

        $loan = CreateLoan::run([
            'member_id' => $member->id,
            'loan_product_id' => $this->makeProduct()->id,
            'type' => 'regular',
            'amount' => 100000,
            'interest_rate' => 5,
            'tenure_months' => 12,
        ]);
        app(LoanService::class)->generateRepaymentSchedules($loan);
        ApproveLoan::run($loan->fresh());
        DisburseLoan::run($loan->fresh());

        $loan->update(['status' => 'completed', 'outstanding' => 0]);
        LoanApprovalLog::record($loan->id, 'status_change', 'repaying', 'completed', 'Loan completed.');

        $timeline = $loan->fresh()->load('approvalLogs.user')->lifecycleTimeline();
        $titles = array_column($timeline, 'title');

        $this->assertContains('Loan completed', $titles);
    }
}
