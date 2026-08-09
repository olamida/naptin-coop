<?php

namespace Tests\Feature;

use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\DisburseLoan;
use App\Models\ApprovalWorkflow;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\PeriodClose;
use App\Models\Region;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\LoanService;
use App\Services\ProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CbnComplianceTest extends TestCase
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

    private function admin(): array
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'super-admin']));
        Permission::firstOrCreate(['name' => 'manage-users']);
        $admin->givePermissionTo('manage-users');
        $token = 'test-session-'.uniqid();
        $admin->forceFill(['active_session_token' => $token])->save();

        return [$admin, $token];
    }

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
            'max_term_months' => 24,
            'max_loans_per_member' => 3,
            'max_total_amount_per_member' => 4000000,
            'requires_guarantors' => false,
            'requires_collateral' => false,
            'enabled' => true,
        ]);
    }

    private function postIncomeAndExpense(float $income, float $expense): void
    {
        $ledger = new LedgerService;
        $entryDate = now()->toDateString();

        $ledger->post('Period income', 'test', null, [
            ['account_code' => LedgerService::INTEREST_INCOME, 'debit' => 0, 'credit' => $income],
            ['account_code' => LedgerService::CASH, 'debit' => $income, 'credit' => 0],
        ], null, null, $entryDate);

        $ledger->post('Period expense', 'test', null, [
            ['account_code' => LedgerService::PROCUREMENT_EXPENSE, 'debit' => $expense, 'credit' => 0],
            ['account_code' => LedgerService::CASH, 'debit' => 0, 'credit' => $expense],
        ], null, null, $entryDate);
    }

    // ------------------------------------------------ CBN appropriations at period close

    public function test_period_close_posts_statutory_reserve_and_education_fund_appropriations(): void
    {
        [$admin, $token] = $this->admin();
        $this->actingAs($admin)->withSession(['active_session_token' => $token]);

        $this->postIncomeAndExpense(100000, 10000); // net profit = 90,000
        $period = now()->format('Y-m');

        $this->post(route('finance.period-close.store'), ['period' => $period])
            ->assertRedirect()
            ->assertSessionHas('success');

        $ledger = new LedgerService;

        $this->assertEquals(22500.00, $ledger->getBalance(LedgerService::GENERAL_RESERVE));   // 25% of 90,000
        $this->assertEquals(2250.00, $ledger->getBalance(LedgerService::EDUCATION_FUND));     // 2.5% of 90,000
        $this->assertEquals(-24750.00, $ledger->getBalance(LedgerService::RETAINED_EARNINGS)); // appropriation debits
        $this->assertTrue(PeriodClose::isClosed($period));
        $this->assertEmpty($ledger->verifyHashChain());
    }

    public function test_period_close_skips_appropriation_when_no_profit(): void
    {
        [$admin, $token] = $this->admin();
        $this->actingAs($admin)->withSession(['active_session_token' => $token]);
        $period = now()->format('Y-m');

        $this->post(route('finance.period-close.store'), ['period' => $period])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('journal_entries', ['reference_type' => 'period_close']);
        $this->assertTrue(PeriodClose::isClosed($period));
    }

    public function test_reclosing_period_does_not_double_appropriate(): void
    {
        [$requester, $token] = $this->admin();
        $this->actingAs($requester)->withSession(['active_session_token' => $token]);

        $this->postIncomeAndExpense(100000, 10000);
        $period = now()->format('Y-m');

        $this->post(route('finance.period-close.store'), ['period' => $period])->assertSessionHas('success');
        $this->post(route('finance.period-close.reopen', $period), ['reason' => 'Correction needed'])->assertSessionHas('success');

        [$approverA, $tokenA] = $this->admin();
        $this->actingAs($approverA)->withSession(['active_session_token' => $tokenA])
            ->post(route('finance.period-close.reopen.approve', $period))
            ->assertSessionHas('success');

        [$approverB, $tokenB] = $this->admin();
        $this->actingAs($approverB)->withSession(['active_session_token' => $tokenB])
            ->post(route('finance.period-close.reopen.approve', $period))
            ->assertSessionHas('success');

        $this->assertFalse(PeriodClose::isClosed($period));

        $this->actingAs($requester)->withSession(['active_session_token' => $token])
            ->post(route('finance.period-close.store'), ['period' => $period])
            ->assertSessionHas('success');

        $ledger = new LedgerService;

        $this->assertEquals(22500.00, $ledger->getBalance(LedgerService::GENERAL_RESERVE));
        $this->assertEquals(2250.00, $ledger->getBalance(LedgerService::EDUCATION_FUND));
    }

    // ------------------------------------------------ Dividend declaration gates

    public function test_dividend_declaration_blocked_when_provision_coverage_below_100_percent(): void
    {
        [$admin, $token] = $this->admin();
        $this->actingAs($admin)->withSession(['active_session_token' => $token]);

        $member = $this->makeMember('STAFF-DIV-'.substr(uniqid(), -6));
        $product = $this->makeProduct();
        $loan = CreateLoan::run([
            'member_id' => $member->id,
            'loan_product_id' => $product->id,
            'type' => $product->slug,
            'amount' => 100000,
            'interest_rate' => 5,
            'tenure_months' => 12,
        ]);
        $loan->update(['status' => 'approved']);
        DisburseLoan::run($loan->fresh());

        $this->post(route('dividends.store'), ['year' => now()->year, 'total_profit' => 10000])
            ->assertRedirect()
            ->assertSessionHasErrors('error');

        $this->assertDatabaseMissing('dividends', ['year' => now()->year]);
    }

    public function test_dividend_declaration_blocked_when_trial_balance_unbalanced(): void
    {
        [$admin, $token] = $this->admin();
        $this->actingAs($admin)->withSession(['active_session_token' => $token]);

        $ledger = new LedgerService;
        $account = $ledger->ensureAccount(LedgerService::CASH);

        $entry = JournalEntry::create([
            'entry_number' => 'JE-UNBAL-'.substr(uniqid(), -6),
            'entry_date' => now()->toDateString(),
            'period' => now()->format('Y-m'),
            'description' => 'Unbalanced test entry',
            'status' => 'posted',
            'uuid' => (string) Str::uuid(),
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $account->id,
            'debit' => 100,
            'credit' => 0,
        ]);

        $this->assertFalse($ledger->trialBalanceIsBalanced());

        $this->post(route('dividends.store'), ['year' => now()->year, 'total_profit' => 10000])
            ->assertRedirect()
            ->assertSessionHasErrors('error');

        $this->assertDatabaseMissing('dividends', ['year' => now()->year]);
    }

    public function test_dividend_declaration_allowed_when_provisioned_and_balanced(): void
    {
        [$admin, $token] = $this->admin();
        $this->actingAs($admin)->withSession(['active_session_token' => $token]);

        $member = $this->makeMember('STAFF-DIVOK-'.substr(uniqid(), -6));
        $product = $this->makeProduct();
        $loan = CreateLoan::run([
            'member_id' => $member->id,
            'loan_product_id' => $product->id,
            'type' => $product->slug,
            'amount' => 100000,
            'interest_rate' => 5,
            'tenure_months' => 12,
        ]);
        $loan->update(['status' => 'approved']);
        DisburseLoan::run($loan->fresh());

        ProvisioningService::calculate();

        $this->post(route('dividends.store'), ['year' => now()->year, 'total_profit' => 10000])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('dividends', ['year' => now()->year, 'total_profit' => 10000]);
    }

    // ------------------------------------------------ CBN single obligor limit

    public function test_single_obligor_limit_blocks_excessive_exposure_and_allows_within_limit(): void
    {
        $members = collect(range(1, 21))
            ->map(fn ($i) => $this->makeMember('STAFF-SO'.$i.'-'.substr(uniqid(), -6)));

        $seq = 1;
        foreach ($members->take(20) as $member) {
            Loan::create([
                'member_id' => $member->id,
                'loan_number' => 'REG/'.now()->year.'/'.str_pad((string) $seq++, 6, '0', STR_PAD_LEFT),
                'type' => 'regular',
                'amount' => 100000,
                'interest_rate' => 5,
                'tenure_months' => 12,
                'monthly_repayment' => 8750,
                'outstanding' => 100000,
                'application_date' => now()->toDateString(),
                'status' => 'disbursed',
            ]);
        }

        $applicant = $members->last();
        $product = $this->makeProduct();
        $service = new LoanService;

        // Exactly 5% of a 2,000,000 portfolio (100,000) is allowed.
        $this->assertNull($service->validateLoanProduct($product, $applicant->id, 100000, 12));

        // Any exposure over 5% is blocked.
        $error = $service->validateLoanProduct($product, $applicant->id, 100001, 12);
        $this->assertNotNull($error);
        $this->assertStringContainsString('single obligor', strtolower($error));
    }
}
