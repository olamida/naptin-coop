<?php

namespace Tests\Feature;

use App\Actions\Loans\CreateLoan;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\PeriodClose;
use App\Models\Region;
use App\Models\User;
use App\Services\LedgerService;
use Database\Seeders\LedgerAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NoBackDatingTest extends TestCase
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

    private function actingAdmin(): array
    {
        [$admin, $token] = $this->admin();

        return [$this->withSession(['active_session_token' => $token])->actingAs($admin), $admin];
    }

    private function seedCash(float $amount): void
    {
        $this->seed(LedgerAccountsSeeder::class);
        app(LedgerService::class)->postSimple('Test opening balance', 'opening', 1, LedgerService::CASH, LedgerService::RETAINED_EARNINGS, $amount);
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
            'max_term_months' => 12,
            'max_loans_per_member' => 3,
            'max_total_amount_per_member' => 4000000,
            'requires_guarantors' => false,
            'requires_collateral' => false,
            'enabled' => true,
        ]);
    }

    public function test_future_count_date_is_rejected_on_cash_count(): void
    {
        [$http, $admin] = $this->actingAdmin();
        $this->seedCash(5000);

        $http->post(route('finance.cash-count.store'), [
            'count_date' => now()->addDay()->toDateString(),
            'physical_count' => 5000,
        ])
            ->assertSessionHasErrors('count_date');

        $this->assertDatabaseMissing('cash_counts', ['id' => 1]);
    }

    public function test_count_date_on_or_before_last_closed_period_is_rejected(): void
    {
        [$http, $admin] = $this->actingAdmin();
        $this->seedCash(5000);

        PeriodClose::create([
            'period' => now()->subMonth()->format('Y-m'),
            'closed_at' => now(),
            'closed_by' => $admin->id,
            'is_closed' => true,
        ]);

        $http->post(route('finance.cash-count.store'), [
            'count_date' => now()->subMonth()->format('Y-m-d'),
            'physical_count' => 5000,
        ])
            ->assertSessionHasErrors('count_date');

        $this->assertDatabaseMissing('cash_counts', ['id' => 1]);
    }

    public function test_today_after_last_closed_period_is_allowed(): void
    {
        [$http, $admin] = $this->actingAdmin();
        $this->seedCash(5000);

        PeriodClose::create([
            'period' => now()->subMonth()->format('Y-m'),
            'closed_at' => now(),
            'closed_by' => $admin->id,
            'is_closed' => true,
        ]);

        $http->post(route('finance.cash-count.store'), [
            'count_date' => now()->toDateString(),
            'physical_count' => 5000,
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('cash_counts', 1);
    }

    public function test_future_payment_date_is_rejected_on_loan_repayment(): void
    {
        [$http, $admin] = $this->actingAdmin();

        $member = $this->makeMember('STAFF-'.substr(uniqid(), -6));
        $product = $this->makeProduct();

        $loan = CreateLoan::run([
            'member_id' => $member->id,
            'loan_product_id' => $product->id,
            'type' => 'regular',
            'amount' => 100000,
            'interest_rate' => 5,
            'tenure_months' => 12,
        ]);

        $response = $http->post(route('loans.repayment.store', ['loan' => $loan->id]), [
            'amount' => 1000,
            'payment_method' => 'cash',
            'payment_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('payment_date');
    }

    public function test_future_entry_date_is_rejected_on_journal_store(): void
    {
        [$http, $admin] = $this->actingAdmin();
        $this->seed(LedgerAccountsSeeder::class);

        $http->post(route('ledger.journals.store'), [
            'description' => 'Test',
            'entry_date' => now()->addDay()->toDateString(),
        ])
            ->assertSessionHasErrors('entry_date');
    }

    public function test_get_requests_are_unaffected(): void
    {
        [$http, $admin] = $this->actingAdmin();

        $http->get(route('finance.cash-count'))->assertOk();
    }
}
