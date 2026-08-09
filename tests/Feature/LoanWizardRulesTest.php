<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\Region;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanWizardRulesTest extends TestCase
{
    use RefreshDatabase;

    private function makeMember(string $staffId, float $savings = 2000000): Member
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
            'balance' => $savings,
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
            'requires_guarantors' => true,
            'requires_collateral' => false,
            'enabled' => true,
        ]);
    }

    public function test_loan_amount_capped_at_three_times_savings(): void
    {
        $member = $this->makeMember('STAFF-3X'.substr(uniqid(), -6), savings: 100000);
        $product = $this->makeProduct();
        $service = new LoanService;

        // 3 × 100,000 = 300,000 is allowed.
        $this->assertNull($service->validateLoanProduct($product, $member->id, 300000, 12));

        // Anything over 300,000 is blocked by the savings rule.
        $error = $service->validateLoanProduct($product, $member->id, 300001, 12);
        $this->assertNotNull($error);
        $this->assertStringContainsString('3× savings', $error);
    }

    public function test_member_without_savings_account_cannot_take_loan(): void
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);
        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'STAFF-NOSAV-'.substr(uniqid(), -6),
            'first_name' => 'Test',
            'last_name' => 'Member',
            'monthly_salary' => 250000,
            'status' => 'active',
        ]);

        $service = new LoanService;

        $error = $service->validateLoanProduct($this->makeProduct(), $member->id, 50000, 12);
        $this->assertNotNull($error);
        $this->assertStringContainsString('maximum eligible loan of ₦0.00', $error);
    }

    public function test_guarantor_exposure_cap_blocks_excessive_guarantee(): void
    {
        $applicant = $this->makeMember('STAFF-APP'.substr(uniqid(), -6), savings: 5000000);
        $guarantor = $this->makeMember('STAFF-GUA'.substr(uniqid(), -6), savings: 5000000);

        // Guarantor already guarantees two ₦300,000 loans (600,000 outstanding).
        foreach ([1, 2] as $i) {
            $loan = Loan::create([
                'member_id' => $this->makeMember('STAFF-BOR'.$i.'-'.substr(uniqid(), -6))->id,
                'loan_number' => 'REG/'.now()->year.'/'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'type' => 'regular',
                'amount' => 300000,
                'interest_rate' => 5,
                'tenure_months' => 12,
                'monthly_repayment' => 26250,
                'outstanding' => 300000,
                'application_date' => now()->toDateString(),
                'status' => 'disbursed',
            ]);
            LoanGuarantor::create([
                'loan_id' => $loan->id,
                'member_id' => $guarantor->id,
                'status' => 'accepted',
            ]);
        }

        $service = new LoanService;
        $product = $this->makeProduct();

        // Applicant's own loan passes both the 3× savings rule and the single-obligor
        // limit (30,000 is exactly 5% of the 600,000 portfolio).
        $this->assertNull($service->validateLoanProduct($product, $applicant->id, 30000, 12));

        // But with the guarantor's 600,000 existing exposure, guaranteeing this
        // loan (30,000 → 630,000) blows past the ₦500,000 cap.
        $error = $service->validateLoanProduct($product, $applicant->id, 30000, 12, [$guarantor->id]);
        $this->assertNotNull($error);
        $this->assertStringContainsString('Guarantor exposure cap', $error);
    }

    public function test_guarantee_within_cap_is_allowed(): void
    {
        $applicant = $this->makeMember('STAFF-APP2'.substr(uniqid(), -6), savings: 5000000);
        $guarantor = $this->makeMember('STAFF-GUA2'.substr(uniqid(), -6), savings: 5000000);

        $service = new LoanService;

        $this->assertNull($service->validateLoanProduct($this->makeProduct(), $applicant->id, 200000, 12, [$guarantor->id]));
    }
}
