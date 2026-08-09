<?php

namespace Tests\Feature;

use App\Actions\Dividends\CalculateDividend;
use App\Actions\Dividends\DeclareDividend;
use App\Actions\Dividends\DistributeDividend;
use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\DisburseLoan;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\Region;
use App\Models\ShareAccount;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanProcessingFeeAndDividendAccrualTest extends TestCase
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

    public function test_create_loan_captures_processing_fee_from_product(): void
    {
        $member = $this->makeMember('STAFF'.substr(uniqid(), -6));

        $product = LoanProduct::create([
            'name' => 'Regular Loan',
            'slug' => 'regular-'.substr(uniqid(), -6),
            'min_amount' => 1000,
            'max_amount' => 2000000,
            'interest_rate' => 5,
            'processing_fee_pct' => 1.00,
            'repayment_method' => 'monthly',
            'max_term_months' => 24,
            'max_loans_per_member' => 2,
            'max_total_amount_per_member' => 4000000,
            'requires_guarantors' => false,
            'requires_collateral' => false,
            'enabled' => true,
        ]);

        $loan = CreateLoan::run([
            'member_id' => $member->id,
            'loan_product_id' => $product->id,
            'type' => $product->slug,
            'amount' => 100000,
            'interest_rate' => 5,
            'tenure_months' => 12,
        ]);

        $this->assertEquals(1000.00, (float) $loan->processing_fee); // 1% of 100,000
        $this->assertEquals(100000.00, (float) $loan->amount);
    }

    public function test_loan_disbursement_posts_net_payout_and_fee_income(): void
    {
        $user = User::create([
            'name' => 'Loan Officer',
            'email' => 'lo-'.substr(uniqid(), -6).'@naptin.coop',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        $member = $this->makeMember('STAFF'.substr(uniqid(), -6));

        $product = LoanProduct::create([
            'name' => 'Regular Loan',
            'slug' => 'regular-'.substr(uniqid(), -6),
            'min_amount' => 1000,
            'max_amount' => 2000000,
            'interest_rate' => 5,
            'processing_fee_pct' => 2.00,
            'repayment_method' => 'monthly',
            'max_term_months' => 24,
            'max_loans_per_member' => 2,
            'max_total_amount_per_member' => 4000000,
            'requires_guarantors' => false,
            'requires_collateral' => false,
            'enabled' => true,
        ]);

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

        $ledger = new LedgerService;

        $this->assertEquals(100000.00, $ledger->getBalance(LedgerService::LOANS_RECEIVABLE));
        $this->assertEquals(2000.00, $ledger->getBalance(LedgerService::PROCESSING_FEES_INCOME));
        $this->assertEquals(-98000.00, $ledger->getBalance(LedgerService::CASH)); // net payout = principal − fee
        $this->assertEmpty($ledger->verifyHashChain());
    }

    public function test_dividend_accrual_creates_payable_and_payout_clears_it(): void
    {
        $user = User::create([
            'name' => 'Treasurer',
            'email' => 'treasurer-'.substr(uniqid(), -6).'@naptin.coop',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        $memberA = $this->makeMember('STAFF-A'.substr(uniqid(), -4));
        $memberB = $this->makeMember('STAFF-B'.substr(uniqid(), -4));

        ShareAccount::create([
            'member_id' => $memberA->id,
            'total_shares' => 60,
            'total_value' => 6000,
            'share_price' => 100,
            'status' => 'active',
        ]);
        ShareAccount::create([
            'member_id' => $memberB->id,
            'total_shares' => 40,
            'total_value' => 4000,
            'share_price' => 100,
            'status' => 'active',
        ]);

        $ledger = new LedgerService;

        $dividend = DeclareDividend::run([
            'year' => now()->year,
            'total_profit' => 10000,
        ]);

        CalculateDividend::run($dividend);

        $this->assertEquals(10000.00, (float) $dividend->fresh()->total_distributed);
        $this->assertEquals(10000.00, $ledger->getBalance(LedgerService::DIVIDENDS_PAYABLE));
        $this->assertEquals(-10000.00, $ledger->getBalance(LedgerService::RETAINED_EARNINGS));

        // Approve then distribute — payout must clear the payable, not hit retained earnings again.
        $dividend->fresh()->update(['status' => 'approved']);
        DistributeDividend::run($dividend->fresh());

        $this->assertEquals(0.00, $ledger->getBalance(LedgerService::DIVIDENDS_PAYABLE));
        $this->assertEquals(-10000.00, $ledger->getBalance(LedgerService::RETAINED_EARNINGS));
        $this->assertEquals(-10000.00, $ledger->getBalance(LedgerService::CASH));
        $this->assertEmpty($ledger->verifyHashChain());
    }
}
