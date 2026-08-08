<?php

namespace Tests\Feature;

use App\Actions\Payroll\CompileAndLockPayroll;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollLedgerPostingTest extends TestCase
{
    use RefreshDatabase;

    private function activeMember(): Member
    {
        $region = Region::create(['name' => 'R', 'code' => 'R1', 'state' => 'S', 'enabled' => true]);

        return Member::create([
            'region_id' => $region->id,
            'staff_id' => 'PAY-'.substr(uniqid(), -6),
            'first_name' => 'Payroll',
            'last_name' => 'Member',
            'status' => 'active',
            'monthly_salary' => 100000,
        ]);
    }

    public function test_compiling_payroll_posts_expected_deductions_to_ledger(): void
    {
        $member = $this->activeMember();

        Loan::create([
            'member_id' => $member->id,
            'loan_number' => 'LN/PAY/001',
            'type' => 'regular',
            'amount' => 200000,
            'interest_rate' => 5,
            'tenure_months' => 12,
            'monthly_repayment' => 20000,
            'outstanding' => 200000,
            'application_date' => '2026-07-01',
            'status' => 'disbursed',
        ]);

        $product = Product::create([
            'name' => 'Saving Box',
            'unit_price' => 15000,
            'stock_quantity' => 10,
            'enabled' => true,
        ]);

        PurchaseOrder::create([
            'order_number' => 'ORD/PAY/001',
            'order_group' => 'GRP/PAY/001',
            'member_id' => $member->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 15000,
            'total_amount' => 15000,
            'payment_type' => 'hire_purchase',
            'monthly_repayment' => 15000,
            'status' => 'approved',
        ]);

        $payroll = CompileAndLockPayroll::run(2026, 8);

        // savings 10% = 10000, shares 5% = 5000, loan 20000, purchase 15000
        $this->assertEquals(50000, $payroll->grand_total);

        $entry = JournalEntry::where('reference_type', 'payroll')
            ->where('reference_id', $payroll->id)
            ->firstOrFail();
        $lines = $entry->lines()->with('account')->get();

        $this->assertSame('posted', $entry->status);

        $receivable = $lines->firstWhere('account.code', '1501');
        $savings = $lines->firstWhere('account.code', '2001');
        $loans = $lines->firstWhere('account.code', '1101');
        $shares = $lines->firstWhere('account.code', '2101');
        $purchases = $lines->firstWhere('account.code', '1201');

        $this->assertNotNull($receivable);
        $this->assertEquals(50000, $receivable->debit);
        $this->assertEquals(10000, $savings->credit);
        $this->assertEquals(20000, $loans->credit);
        $this->assertEquals(5000, $shares->credit);
        $this->assertEquals(15000, $purchases->credit);
    }

    public function test_compiling_payroll_without_obligations_posts_savings_and_shares_only(): void
    {
        $this->activeMember();

        $payroll = CompileAndLockPayroll::run(2026, 9);

        $this->assertEquals(15000, $payroll->grand_total);

        $entry = JournalEntry::where('reference_type', 'payroll')
            ->where('reference_id', $payroll->id)
            ->firstOrFail();
        $lines = $entry->lines()->with('account')->get();

        $receivable = $lines->firstWhere('account.code', '1501');
        $savings = $lines->firstWhere('account.code', '2001');
        $shares = $lines->firstWhere('account.code', '2101');

        $this->assertEquals(15000, $receivable->debit);
        $this->assertEquals(10000, $savings->credit);
        $this->assertEquals(5000, $shares->credit);
        $this->assertNull($lines->firstWhere('account.code', '1101'));
        $this->assertNull($lines->firstWhere('account.code', '1201'));
    }
}
