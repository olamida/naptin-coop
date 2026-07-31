<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\Member;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Services\SavingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeMember(string $status = 'active'): Member
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR' . strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'STAFF' . substr(uniqid(), -6),
            'first_name' => 'Test',
            'last_name' => 'Member',
            'email' => null,
            'monthly_salary' => 100000,
            'status' => $status,
        ]);

        SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV-' . substr(uniqid(), -8),
            'balance' => 0,
            'status' => 'active',
        ]);

        return $member;
    }

    public function test_deposit_with_evidence_auto_approves_and_credits_balance(): void
    {
        $member = $this->makeMember();
        $service = app(SavingsService::class);

        $txn = $service->recordDeposit($member->id, 50000, 'Monthly contribution', 'manual', 'evidence.jpg');

        $this->assertSame('completed', $txn->status);
        $this->assertSame('deposit', $txn->type);
        $this->assertEquals(0, $txn->balance_before);
        $this->assertEquals(50000, $txn->balance_after);

        $account = $member->savingsAccount->fresh();
        $this->assertEquals(50000, $account->balance);

        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'savings',
            'reference_id' => $txn->id,
            'status' => 'posted',
        ]);
        $this->assertEquals(2, JournalEntry::latest('id')->first()->lines()->count());
    }

    public function test_deposit_without_evidence_creates_pending_transaction_without_balance_change(): void
    {
        $member = $this->makeMember();
        $service = app(SavingsService::class);

        $txn = $service->recordDeposit($member->id, 50000, 'Portal deposit');

        $this->assertSame('pending', $txn->status);
        $this->assertEquals(0, $txn->balance_after);
        $this->assertEquals(0, $member->savingsAccount->fresh()->balance);
        $this->assertDatabaseMissing('journal_entries', ['reference_id' => $txn->id]);
    }

    public function test_large_deposit_above_auto_approve_limit_requires_approval(): void
    {
        $member = $this->makeMember();
        $service = app(SavingsService::class);

        $txn = $service->recordDeposit($member->id, 250000, 'Large deposit', 'manual', 'evidence.jpg');

        $this->assertSame('pending', $txn->status);
        $this->assertEquals(0, $member->savingsAccount->fresh()->balance);
    }

    public function test_inactive_member_deposit_requires_approval(): void
    {
        $member = $this->makeMember('suspended');
        $service = app(SavingsService::class);

        $txn = $service->recordDeposit($member->id, 10000, null, 'manual', 'evidence.jpg');

        $this->assertSame('pending', $txn->status);
    }

    public function test_withdrawal_request_is_pending_and_does_not_change_balance(): void
    {
        $member = $this->makeMember();
        $account = $member->savingsAccount;
        $account->update(['balance' => 100000]);

        $service = app(SavingsService::class);
        $txn = $service->recordWithdrawalRequest($member->id, 30000, 'Travel advance');

        $this->assertSame('withdrawal', $txn->type);
        $this->assertSame('pending', $txn->status);
        $this->assertEquals(100000, $txn->balance_before);
        $this->assertEquals(100000, $txn->balance_after);
        $this->assertEquals(100000, $member->savingsAccount->fresh()->balance);
    }

    public function test_approving_withdrawal_deducts_balance_and_posts_ledger(): void
    {
        $member = $this->makeMember();
        $account = $member->savingsAccount;
        $account->update(['balance' => 100000]);

        $service = app(SavingsService::class);
        $txn = $service->recordWithdrawalRequest($member->id, 30000);
        $service->approveWithdrawal($txn);

        $this->assertSame('completed', $txn->fresh()->status);
        $this->assertEquals(70000, $member->savingsAccount->fresh()->balance);

        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'savings',
            'reference_id' => $txn->id,
        ]);
    }

    public function test_approving_withdrawal_over_balance_throws(): void
    {
        $member = $this->makeMember();
        $member->savingsAccount->update(['balance' => 5000]);

        $service = app(SavingsService::class);
        $txn = $service->recordWithdrawalRequest($member->id, 10000);

        $this->expectException(\RuntimeException::class);

        $service->approveWithdrawal($txn);
    }

    public function test_approving_pending_deposit_credits_balance_and_posts_ledger(): void
    {
        $member = $this->makeMember();
        $service = app(SavingsService::class);

        $txn = $service->recordDeposit($member->id, 50000, 'Portal deposit');
        $service->approveDeposit($txn);

        $this->assertSame('completed', $txn->fresh()->status);
        $this->assertEquals(50000, $member->savingsAccount->fresh()->balance);

        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'savings',
            'reference_id' => $txn->id,
        ]);
    }

    public function test_health_score_ratio(): void
    {
        $member = $this->makeMember();
        $member->savingsAccount->update(['balance' => 100000]);

        $service = app(SavingsService::class);

        $this->assertEquals(10000000.0, $service->calculateHealthScore($member));
    }
}
