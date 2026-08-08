<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\User;
use App\Services\LedgerService;
use Database\Seeders\LedgerAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SavingsControlReportTest extends TestCase
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
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);
    }

    private function makeAccount(Member $member, float $balance, string $number): SavingsAccount
    {
        return SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => $number,
            'balance' => $balance,
            'interest_rate' => 0,
            'status' => 'active',
        ]);
    }

    private function deposit(SavingsAccount $account, float $amount, float $before): void
    {
        SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'reference' => 'SAV/DEP/'.substr(uniqid(), -6),
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $before + $amount,
            'status' => 'completed',
            'transaction_date' => now(),
        ]);
    }

    public function test_report_shows_member_ledger_and_reconciled_control(): void
    {
        [$admin, $token] = $this->admin();

        $memberA = $this->makeMember('STAFF-A'.substr(uniqid(), -4));
        $memberB = $this->makeMember('STAFF-B'.substr(uniqid(), -4));

        $accountA = $this->makeAccount($memberA, 1500, 'ACC-A'.substr(uniqid(), -4));
        $accountB = $this->makeAccount($memberB, 800, 'ACC-B'.substr(uniqid(), -4));

        $this->deposit($accountA, 1000, 0);
        $this->deposit($accountA, 500, 1000);
        $this->deposit($accountB, 800, 0);

        // Seed the ledger so the control account reconciles with the sub-ledger.
        $this->seed(LedgerAccountsSeeder::class);
        app(LedgerService::class)->postSimple(
            'Test savings control',
            'opening',
            1,
            LedgerService::CASH,
            LedgerService::MEMBERS_SAVINGS,
            2300
        );

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->get(route('finance.savings-control'))
            ->assertOk()
            ->assertSee('Members Savings Control Report')
            ->assertSee($memberA->staff_id)
            ->assertSee($memberB->staff_id)
            ->assertSee('₦1,500.00')   // member A closing balance
            ->assertSee('₦800.00');    // member B closing balance
    }

    public function test_report_flags_control_and_per_member_variance(): void
    {
        [$admin, $token] = $this->admin();

        $member = $this->makeMember('STAFF-C'.substr(uniqid(), -4));
        $this->makeAccount($member, 5000, 'ACC-C'.substr(uniqid(), -4)); // no transactions

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->get(route('finance.savings-control'))
            ->assertOk()
            ->assertSee('+₦5,000.00'); // control variance and per-member ledger variance
    }

    public function test_guest_cannot_access_savings_control_report(): void
    {
        $this->get(route('finance.savings-control'))->assertRedirect(route('login'));
    }
}
