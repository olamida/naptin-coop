<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\PeriodClose;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\LedgerSyncService;
use Database\Seeders\LedgerAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerChartOfAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_full_cbn_chart_with_control_flags(): void
    {
        $this->seed(LedgerAccountsSeeder::class);

        $this->assertSame(35, ChartOfAccount::count());

        // Control accounts carry the right module flags.
        $this->assertTrue(ChartOfAccount::where('code', '1301')->value('is_control_account'));
        $this->assertSame('inventory', ChartOfAccount::where('code', '1301')->value('control_module'));
        $this->assertTrue(ChartOfAccount::where('code', '1501')->value('is_control_account'));
        $this->assertTrue(ChartOfAccount::where('code', '2201')->value('is_control_account'));
        $this->assertSame('dividends', ChartOfAccount::where('code', '2201')->value('control_module'));

        // Manual-entry-restricted accounts.
        $this->assertFalse(ChartOfAccount::where('code', '1205')->value('allow_manual_entry'));
        $this->assertFalse(ChartOfAccount::where('code', '4004')->value('allow_manual_entry'));
        $this->assertTrue(ChartOfAccount::where('code', '5003')->value('allow_manual_entry'));

        // Income/expense/equity breadth required by the pending features.
        $this->assertSame('income', ChartOfAccount::where('code', '4004')->value('type'));
        $this->assertSame('equity', ChartOfAccount::where('code', '3002')->value('type'));
        $this->assertSame('asset', ChartOfAccount::where('code', '1005')->value('type'));
    }

    public function test_seeder_is_idempotent_and_preserves_existing_operational_accounts(): void
    {
        $this->seed(LedgerAccountsSeeder::class);

        // Simulate a pre-existing operational account that predates the CBN chart.
        ChartOfAccount::create([
            'code' => '9999',
            'name' => 'Legacy Operational Account',
            'type' => 'expense',
            'normal_side' => 'debit',
        ]);

        $this->seed(LedgerAccountsSeeder::class);

        $this->assertSame(36, ChartOfAccount::count());
        $this->assertSame('Legacy Operational Account', ChartOfAccount::where('code', '9999')->value('name'));

        // Existing CBN-coded accounts keep their operational names, not spec names.
        $this->assertSame('Loans Receivable', ChartOfAccount::where('code', '1101')->value('name'));
        $this->assertSame('Retained Earnings', ChartOfAccount::where('code', '3001')->value('name'));
    }

    public function test_ensure_account_creates_cbn_codes_on_demand(): void
    {
        $ledger = new LedgerService;

        $account = $ledger->ensureAccount('4004');

        $this->assertNotNull($account->id);
        $this->assertSame('income', $account->type);
        $this->assertSame('credit', $account->normal_side);
        $this->assertFalse($account->allow_manual_entry);

        $this->expectException(\RuntimeException::class);
        $ledger->ensureAccount('9999');
    }

    public function test_get_balance_honours_date_range(): void
    {
        $ledger = new LedgerService;
        $ledger->postSimple('Test deposit', 'savings', 1, LedgerService::CASH, LedgerService::MEMBERS_SAVINGS, 100.00);

        $today = now()->toDateString();

        $this->assertEquals(100.00, $ledger->getBalance(LedgerService::MEMBERS_SAVINGS));
        $this->assertEquals(100.00, $ledger->getBalance(LedgerService::MEMBERS_SAVINGS, now()->subDay()->toDateString(), now()->addDay()->toDateString()));
        $this->assertEquals(0.00, $ledger->getBalance(LedgerService::MEMBERS_SAVINGS, '2020-01-01', '2020-01-31'));
        $this->assertEquals(100.00, $ledger->getBalance(LedgerService::MEMBERS_SAVINGS, null, $today));
    }

    public function test_is_period_closed_reflects_period_closes_table(): void
    {
        $user = User::create([
            'name' => 'Treasurer',
            'email' => 'treasurer-'.substr(uniqid(), -6).'@naptin.coop',
            'password' => bcrypt('password'),
        ]);

        PeriodClose::create([
            'period' => now()->format('Y-m'),
            'closed_at' => now(),
            'closed_by' => $user->id,
            'is_closed' => true,
        ]);

        $ledger = new LedgerService;

        $this->assertTrue($ledger->isPeriodClosed(now()->format('Y-m')));
        $this->assertFalse($ledger->isPeriodClosed(now()->subYear()->format('Y-m')));
    }

    public function test_validate_control_accounts_returns_reconciled_rows_after_sync(): void
    {
        // Seed the full chart so control flags exist for every account.
        $this->seed(LedgerAccountsSeeder::class);

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
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);

        SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV-'.substr(uniqid(), -8),
            'balance' => 250000,
            'status' => 'active',
        ]);

        ShareAccount::create([
            'member_id' => $member->id,
            'total_shares' => 500,
            'total_value' => 50000,
            'share_price' => 100,
            'status' => 'active',
        ]);

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
            'amount' => 120000,
            'interest_rate' => 5,
            'tenure_months' => 12,
            'monthly_repayment' => 10000,
            'total_repaid' => 0,
            'outstanding' => 120000,
            'application_date' => now()->subDays(30),
            'status' => 'repaying',
        ]);

        app(LedgerSyncService::class)->syncOpeningBalances();

        $ledger = new LedgerService;
        $rows = $ledger->validateControlAccounts();

        $this->assertCount(4, $rows);
        foreach ($rows as $row) {
            $this->assertArrayHasKey('code', $row);
            $this->assertArrayHasKey('variance', $row);
            $this->assertTrue($row['reconciled'], "Control account {$row['code']} should reconcile (variance {$row['variance']}).");
        }
    }
}
