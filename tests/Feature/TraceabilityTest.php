<?php

namespace Tests\Feature;

use App\Actions\Loans\RecordRepayment;
use App\Imports\PurchaseImport;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Services\SavingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraceabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeMember(): Member
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'TRACE'.substr(uniqid(), -6),
            'first_name' => 'Trace',
            'last_name' => 'Member',
            'email' => null,
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);

        SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV-'.substr(uniqid(), -8),
            'balance' => 0,
            'status' => 'active',
        ]);

        return $member;
    }

    public function test_savings_deposit_links_the_posted_journal_entry(): void
    {
        $member = $this->makeMember();
        $service = app(SavingsService::class);

        $txn = $service->recordDeposit($member->id, 50000, 'Monthly contribution', 'manual', 'evidence.jpg');

        $entry = $txn->fresh()->journalEntry;
        $this->assertNotNull($entry);
        $this->assertSame('posted', $entry->status);
        $this->assertSame('savings', $entry->reference_type);
        $this->assertSame($txn->id, $entry->reference_id);
    }

    public function test_savings_withdrawal_links_the_posted_journal_entry(): void
    {
        $member = $this->makeMember();
        $member->savingsAccount->update(['balance' => 100000]);
        $service = app(SavingsService::class);

        $txn = $service->recordWithdrawalRequest($member->id, 30000);
        $service->approveWithdrawal($txn);

        $entry = $txn->fresh()->journalEntry;
        $this->assertNotNull($entry);
        $this->assertSame('posted', $entry->status);
        $this->assertSame($txn->id, $entry->reference_id);
    }

    public function test_pending_deposit_has_no_journal_link(): void
    {
        $member = $this->makeMember();
        $service = app(SavingsService::class);

        $txn = $service->recordDeposit($member->id, 50000, 'Portal deposit');

        $this->assertNull($txn->fresh()->journal_entry_id);
    }

    public function test_loan_repayment_records_fees_portion(): void
    {
        $member = $this->makeMember();
        $loan = Loan::create([
            'member_id' => $member->id,
            'loan_number' => 'LN/TRACE/001',
            'type' => 'regular',
            'amount' => 100000,
            'interest_rate' => 5,
            'tenure_months' => 12,
            'monthly_repayment' => 8750,
            'outstanding' => 100000,
            'application_date' => '2026-07-01',
            'status' => 'disbursed',
        ]);

        RecordRepayment::run($loan, [
            'amount' => 10000,
            'payment_method' => 'cash',
            'payment_date' => '2026-08-05',
        ]);

        $repayment = LoanRepayment::where('loan_id', $loan->id)->firstOrFail();
        $this->assertEquals(0, $repayment->fees_portion);
        $this->assertNotNull($repayment->interest_portion);
        $this->assertNotNull($repayment->principal_portion);
    }

    public function test_purchase_import_stamps_batch_and_external_reference(): void
    {
        $member = $this->makeMember();
        $product = Product::create([
            'name' => 'Rice 50kg',
            'unit_price' => 45000,
            'stock_quantity' => 10,
            'enabled' => true,
        ]);

        $import = new PurchaseImport('batch-abc-123');
        $import->model([
            'staff_id' => $member->staff_id,
            'product_name' => 'Rice 50kg',
            'quantity' => 2,
            'unit_price' => 45000,
            'external_reference' => 'SAL-REF-987',
        ]);

        $order = PurchaseOrder::firstOrFail();
        $this->assertSame('batch-abc-123', $order->import_batch_id);
        $this->assertSame('SAL-REF-987', $order->external_reference);
        $this->assertEquals(90000, $order->total_amount);
    }
}
