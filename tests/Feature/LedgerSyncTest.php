<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use App\Services\LedgerService;
use App\Services\LedgerSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerSyncTest extends TestCase
{
    use RefreshDatabase;

    private function makeMemberWithBalances(float $savings, float $shares, float $loanOutstanding): Member
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'STAFF'.substr(uniqid(), -6),
            'first_name' => 'Test',
            'last_name' => 'Member',
            'email' => null,
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);

        SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV-'.substr(uniqid(), -8),
            'balance' => $savings,
            'status' => 'active',
        ]);

        ShareAccount::create([
            'member_id' => $member->id,
            'total_shares' => $shares / 100,
            'total_value' => $shares,
            'share_price' => 100,
            'status' => 'active',
        ]);

        if ($loanOutstanding > 0) {
            $product = LoanProduct::create([
                'name' => 'Regular Loan',
                'slug' => 'regular-'.substr(uniqid(), -6),
                'min_amount' => 1000,
                'max_amount' => 10000000,
                'interest_rate' => 5,
                'repayment_method' => 'monthly',
                'max_term_months' => 24,
                'max_loans_per_member' => 2,
                'max_total_amount_per_member' => 20000000,
                'requires_guarantors' => false,
                'requires_collateral' => false,
                'enabled' => true,
            ]);

            Loan::create([
                'member_id' => $member->id,
                'loan_product_id' => $product->id,
                'loan_number' => 'LN-'.substr(uniqid(), -6),
                'type' => $product->slug,
                'amount' => $loanOutstanding,
                'interest_rate' => 5,
                'tenure_months' => 12,
                'monthly_repayment' => 10000,
                'total_repaid' => 0,
                'outstanding' => $loanOutstanding,
                'application_date' => now()->subDays(30),
                'status' => 'repaying',
            ]);
        }

        return $member;
    }

    public function test_sync_posts_opening_balances_and_reconciles_control_accounts(): void
    {
        $this->makeMemberWithBalances(250000, 50000, 120000);

        $result = app(LedgerSyncService::class)->syncOpeningBalances();

        $this->assertTrue($result['posted']);
        $this->assertNotEmpty($result['entry_number']);

        $entry = JournalEntry::where('entry_number', $result['entry_number'])->first();
        $this->assertNotNull($entry);
        $this->assertSame('posted', $entry->status);
        $this->assertTrue($entry->isBalanced());

        $ledger = new LedgerService;
        $this->assertEquals(250000, $ledger->getBalance(LedgerService::MEMBERS_SAVINGS));
        $this->assertEquals(120000, $ledger->getBalance(LedgerService::LOANS_RECEIVABLE));
        $this->assertEquals(50000, $ledger->getBalance(LedgerService::SHARE_CAPITAL));

        $this->assertEmpty($ledger->verifyHashChain());
    }

    public function test_sync_is_idempotent_and_does_not_double_count(): void
    {
        $this->makeMemberWithBalances(250000, 50000, 120000);

        $service = app(LedgerSyncService::class);
        $first = $service->syncOpeningBalances();
        $this->assertTrue($first['posted']);

        $second = $service->syncOpeningBalances();
        $this->assertFalse($second['posted']);

        $ledger = new LedgerService;
        $this->assertEquals(250000, $ledger->getBalance(LedgerService::MEMBERS_SAVINGS));
        $this->assertEquals(120000, $ledger->getBalance(LedgerService::LOANS_RECEIVABLE));
        $this->assertEquals(50000, $ledger->getBalance(LedgerService::SHARE_CAPITAL));
    }

    public function test_sync_returns_noop_when_ledger_is_already_in_sync(): void
    {
        $this->makeMemberWithBalances(100000, 0, 0);

        $service = app(LedgerSyncService::class);
        $this->assertTrue($service->syncOpeningBalances()['posted']);

        $again = $service->syncOpeningBalances();
        $this->assertFalse($again['posted']);
        $this->assertStringContainsString('already in sync', $again['message']);
    }
}
