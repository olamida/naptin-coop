<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\PeriodClose;
use App\Models\User;
use App\Notifications\ControlVarianceNotification;
use App\Notifications\LedgerTamperNotification;
use App\Services\DatabaseBackupService;
use App\Services\LedgerService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScheduledFinanceCommandsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'super-admin']));

        return $admin;
    }

    public function test_scheduled_finance_commands_are_registered(): void
    {
        $events = app(Schedule::class)->events();
        $commands = collect($events)->map(fn ($event) => $event->command ?? '');

        $this->assertStringContainsString('app:backup-encrypted', $commands->implode("\n"));
        $this->assertStringContainsString('app:verify-ledger-hash-chain', $commands->implode("\n"));
        $this->assertStringContainsString('app:reconcile-control-accounts', $commands->implode("\n"));
        $this->assertStringContainsString('app:calculate-provisioning', $commands->implode("\n"));
    }

    public function test_backup_encrypted_dumps_encrypts_and_logs(): void
    {
        $backup = $this->mock(DatabaseBackupService::class, function ($mock) {
            $mock->shouldReceive('dumpToTempFile')->once()->andReturn(sys_get_temp_dir().'/naptin-test-dump.sql');
            $mock->shouldReceive('encryptToTempFile')->once()->andReturn(sys_get_temp_dir().'/naptin-test-dump.sql.enc');
            $mock->shouldReceive('storeEncrypted')->once();
            $mock->shouldReceive('deleteTemp')->once();
        });

        $this->artisan('app:backup-encrypted')->assertSuccessful();

        $this->assertTrue(ActivityLog::where('event', 'backup_encrypted')->exists());
    }

    public function test_verify_hash_chain_is_quiet_when_intact(): void
    {
        Notification::fake();
        $this->admin();

        $this->mock(LedgerService::class, function ($mock) {
            $mock->shouldReceive('verifyHashChain')->once()->andReturn([]);
        });

        $this->artisan('app:verify-ledger-hash-chain')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_verify_hash_chain_alerts_admins_on_tamper(): void
    {
        Notification::fake();
        $admin = $this->admin();

        $broken = [
            ['id' => 1, 'entry_number' => 'JE-000001', 'expected_prev_hash' => 'GENESIS', 'stored_prev_hash' => 'deadbeef', 'stored_hash' => 'cafebabe'],
        ];

        $this->mock(LedgerService::class, function ($mock) use ($broken) {
            $mock->shouldReceive('verifyHashChain')->once()->andReturn($broken);
        });

        $this->artisan('app:verify-ledger-hash-chain')->assertSuccessful();

        Notification::assertSentTo($admin, LedgerTamperNotification::class);
        $this->assertTrue(ActivityLog::where('event', 'ledger_tamper_detected')->exists());
    }

    public function test_reconcile_control_accounts_is_quiet_when_reconciled(): void
    {
        Notification::fake();
        $this->admin();

        $this->mock(LedgerService::class, function ($mock) {
            $mock->shouldReceive('validateControlAccounts')->once()->andReturn([
                ['code' => '2001', 'name' => 'Members Savings', 'ledger_balance' => 100.0, 'sub_ledger_balance' => 100.0, 'variance' => 0.0, 'reconciled' => true],
            ]);
        });

        $this->artisan('app:reconcile-control-accounts')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_reconcile_control_accounts_alerts_admins_on_variance(): void
    {
        Notification::fake();
        $admin = $this->admin();

        $this->mock(LedgerService::class, function ($mock) {
            $mock->shouldReceive('validateControlAccounts')->once()->andReturn([
                ['code' => '2001', 'name' => 'Members Savings', 'ledger_balance' => 0.0, 'sub_ledger_balance' => 100.0, 'variance' => -100.0, 'reconciled' => false],
            ]);
        });

        $this->artisan('app:reconcile-control-accounts')->assertSuccessful();

        Notification::assertSentTo($admin, ControlVarianceNotification::class);
        $this->assertTrue(ActivityLog::where('event', 'control_reconciliation_variance')->exists());
    }

    public function test_calculate_provisioning_skips_when_period_is_closed(): void
    {
        $user = $this->admin();

        PeriodClose::create([
            'period' => now()->format('Y-m'),
            'closed_at' => now(),
            'closed_by' => $user->id,
            'is_closed' => true,
        ]);

        $this->artisan('app:calculate-provisioning')->assertSuccessful();

        $this->assertDatabaseMissing('loan_loss_provisions', ['period' => now()->format('Y-m')]);
    }
}
