<?php

namespace Tests\Feature;

use App\Models\CashCount;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\LedgerService;
use Database\Seeders\LedgerAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CashCountTest extends TestCase
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

    private function seedCash(float $amount): void
    {
        $this->seed(LedgerAccountsSeeder::class);
        app(LedgerService::class)->postSimple('Test opening balance', 'opening', 1, LedgerService::CASH, LedgerService::RETAINED_EARNINGS, $amount);
    }

    public function test_admin_can_record_balanced_cash_count(): void
    {
        [$admin, $token] = $this->admin();
        $this->seedCash(5000);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('finance.cash-count.store'), [
                'count_date' => now()->toDateString(),
                'physical_count' => 5000,
                'notes' => 'All good',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cash_counts', [
            'status' => CashCount::STATUS_BALANCED,
            'variance' => 0,
        ]);

        $this->assertEquals(now()->toDateString(), CashCount::first()->count_date->format('Y-m-d'));

        $this->assertEquals(0, JournalEntry::where('reference_type', 'cash_count')->count());
    }

    public function test_shortage_posts_cash_suspense_journal(): void
    {
        [$admin, $token] = $this->admin();
        $this->seedCash(5000);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('finance.cash-count.store'), [
                'count_date' => now()->toDateString(),
                'physical_count' => 4900,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cash_counts', [
            'status' => CashCount::STATUS_SHORTAGE,
            'variance' => -100,
        ]);

        $ledger = new LedgerService;
        $this->assertEquals(100.00, $ledger->getBalance(LedgerService::CASH_SUSPENSE));
        $this->assertEquals(4900.00, $ledger->getBalance(LedgerService::CASH));
        $this->assertEmpty($ledger->verifyHashChain());
    }

    public function test_excess_posts_cash_suspense_journal(): void
    {
        [$admin, $token] = $this->admin();
        $this->seedCash(5000);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('finance.cash-count.store'), [
                'count_date' => now()->toDateString(),
                'physical_count' => 5100,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cash_counts', [
            'status' => CashCount::STATUS_EXCESS,
            'variance' => 100,
        ]);

        $ledger = new LedgerService;
        $this->assertEquals(-100.00, $ledger->getBalance(LedgerService::CASH_SUSPENSE));
        $this->assertEquals(5100.00, $ledger->getBalance(LedgerService::CASH));
        $this->assertEmpty($ledger->verifyHashChain());
    }

    public function test_duplicate_cash_count_for_same_date_is_rejected(): void
    {
        [$admin, $token] = $this->admin();
        $this->seedCash(5000);

        $payload = [
            'count_date' => now()->toDateString(),
            'physical_count' => 5000,
        ];

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('finance.cash-count.store'), $payload)
            ->assertRedirect();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('finance.cash-count.store'), $payload)
            ->assertSessionHasErrors('count_date');
    }

    public function test_admin_can_verify_a_cash_count(): void
    {
        [$admin, $token] = $this->admin();
        $this->seedCash(5000);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('finance.cash-count.store'), [
                'count_date' => now()->toDateString(),
                'physical_count' => 5000,
            ]);

        $count = CashCount::first();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('finance.cash-count.verify', $count))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cash_counts', [
            'id' => $count->id,
            'verified_by' => $admin->id,
        ]);
    }

    public function test_guest_cannot_access_cash_count(): void
    {
        $this->get(route('finance.cash-count'))->assertRedirect(route('login'));
    }
}
