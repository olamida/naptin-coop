<?php

namespace Tests\Feature;

use App\Models\CashCount;
use App\Models\Dividend;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PeriodCloseChecksTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): array
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'super-admin']));
        Permission::firstOrCreate(['name' => 'manage-users']);
        $admin->givePermissionTo('manage-users');
        $token = 'test-session-'.uniqid();
        $admin->forceFill(['active_session_token' => $token])->save();

        return [$admin, $token];
    }

    private function attemptClose(array $adminPair): TestResponse
    {
        [$adminUser, $token] = $adminPair;

        return $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($adminUser)
            ->post(route('finance.period-close.store'), ['period' => now()->format('Y-m')]);
    }

    private function makeMember(): Member
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        return Member::create([
            'region_id' => $region->id,
            'staff_id' => 'STAFF-'.substr(uniqid(), -6),
            'first_name' => 'Test',
            'last_name' => 'Member',
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);
    }

    public function test_close_blocked_when_trial_balance_unbalanced(): void
    {
        $account = (new LedgerService)->ensureAccount(LedgerService::CASH);
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

        $this->attemptClose($this->admin())->assertSessionHasErrors('error');
    }

    public function test_close_blocked_when_cash_count_has_imbalance(): void
    {
        $pair = $this->admin();
        [$admin, $token] = $pair;

        CashCount::create([
            'count_date' => now()->toDateString(),
            'system_balance' => 1000,
            'physical_count' => 900,
            'variance' => -100,
            'status' => CashCount::STATUS_SHORTAGE,
            'counted_by' => $admin->id,
        ]);

        $this->attemptClose($pair)->assertSessionHasErrors('error');
    }

    public function test_close_blocked_when_loan_awaiting_approval(): void
    {
        $member = $this->makeMember();

        Loan::create([
            'member_id' => $member->id,
            'loan_number' => 'LN-'.substr(uniqid(), -6),
            'type' => 'regular',
            'amount' => 50000,
            'interest_rate' => 5,
            'tenure_months' => 12,
            'monthly_repayment' => 4375,
            'outstanding' => 50000,
            'application_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->attemptClose($this->admin())->assertSessionHasErrors('error');
    }

    public function test_close_blocked_when_purchase_order_awaiting_approval(): void
    {
        $member = $this->makeMember();
        $product = Product::create([
            'name' => 'Test Product',
            'unit_price' => 5000,
            'stock_quantity' => 10,
            'enabled' => true,
        ]);

        PurchaseOrder::create([
            'member_id' => $member->id,
            'product_id' => $product->id,
            'order_number' => 'ORD-'.substr(uniqid(), -6),
            'order_group' => 'GRP-'.substr(uniqid(), -6),
            'quantity' => 1,
            'unit_price' => 5000,
            'total_amount' => 5000,
            'payment_type' => 'hire_purchase',
            'status' => 'pending',
        ]);

        $this->attemptClose($this->admin())->assertSessionHasErrors('error');
    }

    public function test_close_blocked_when_dividend_declaration_not_approved(): void
    {
        Dividend::create([
            'dividend_number' => 'DIV/'.now()->year.'/0001',
            'year' => now()->year,
            'total_profit' => 10000,
            'status' => 'draft',
        ]);

        $this->attemptClose($this->admin())->assertSessionHasErrors('error');
    }

    public function test_close_blocked_when_control_account_unreconciled(): void
    {
        $member = $this->makeMember();

        // Savings sub-ledger balance exists but no ledger posting was made.
        SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV-'.substr(uniqid(), -8),
            'balance' => 250000,
            'status' => 'active',
        ]);

        $this->attemptClose($this->admin())->assertSessionHasErrors('error');
    }

    public function test_close_succeeds_when_all_checks_pass(): void
    {
        $this->attemptClose($this->admin())->assertSessionHas('success');
    }
}
