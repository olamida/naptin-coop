<?php

namespace Tests\Feature;

use App\Models\PeriodClose;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LedgerImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_hash_chain_is_built_and_verified_after_multiple_postings(): void
    {
        $ledger = new LedgerService;

        $first = $ledger->postSimple('First entry', 'test', null, LedgerService::CASH, LedgerService::MEMBERS_SAVINGS, 1000);
        $second = $ledger->postSimple('Second entry', 'test', null, LedgerService::CASH, LedgerService::MEMBERS_SAVINGS, 500);

        $this->assertSame('posted', $first->status);
        $this->assertNotNull($first->uuid);
        $this->assertNotNull($first->period);
        $this->assertSame('GENESIS', $first->prev_hash);
        $this->assertNotEmpty($first->hash);

        $this->assertSame($first->hash, $second->prev_hash);
        $this->assertTrue($first->verifyHash());
        $this->assertTrue($second->verifyHash($first->hash));

        $this->assertEmpty($ledger->verifyHashChain());
    }

    public function test_unbalanced_entry_is_rejected(): void
    {
        $ledger = new LedgerService;

        $this->expectException(\RuntimeException::class);

        DB::transaction(function () use ($ledger) {
            $ledger->post('Unbalanced', 'test', null, [
                ['account_code' => LedgerService::CASH, 'debit' => 100, 'credit' => 0],
                ['account_code' => LedgerService::MEMBERS_SAVINGS, 'debit' => 0, 'credit' => 50],
            ]);
        });
    }

    public function test_reversal_creates_linked_entry_and_keeps_original_immutable(): void
    {
        $ledger = new LedgerService;

        $original = $ledger->postSimple('Savings deposit', 'savings', 1, LedgerService::CASH, LedgerService::MEMBERS_SAVINGS, 1000);
        $originalDescription = $original->description;

        $reversal = $ledger->reverse($original, 'Duplicate entry');

        $this->assertSame('posted', $reversal->status);
        $this->assertSame($original->id, $reversal->reversal_of_id);
        $this->assertTrue($reversal->isReversal());
        $this->assertStringStartsWith('REVERSAL of '.$original->entry_number, $reversal->description);
        $this->assertSame($originalDescription, $original->fresh()->description);
        $this->assertSame('posted', $original->fresh()->status);

        $this->assertEmpty($ledger->verifyHashChain());
    }

    public function test_posting_an_entry_inside_a_closed_period_is_blocked(): void
    {
        PeriodClose::create([
            'period' => now()->format('Y-m'),
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => null,
        ]);

        $ledger = new LedgerService;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('closed');

        $ledger->postSimple('Too late', 'test', null, LedgerService::CASH, LedgerService::MEMBERS_SAVINGS, 100);
    }

    public function test_reopening_a_period_allows_postings_again(): void
    {
        $close = PeriodClose::create([
            'period' => now()->format('Y-m'),
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => null,
        ]);

        $close->update([
            'is_closed' => false,
            'reopened_at' => now(),
            'reopened_by' => null,
            'reopen_reason' => 'Correction needed',
        ]);

        $ledger = new LedgerService;
        $entry = $ledger->postSimple('After reopen', 'test', null, LedgerService::CASH, LedgerService::MEMBERS_SAVINGS, 100);

        $this->assertSame('posted', $entry->status);
        $this->assertEmpty($ledger->verifyHashChain());
    }

    public function test_reversal_of_posted_entry_cannot_be_reversed_again(): void
    {
        $ledger = new LedgerService;

        $original = $ledger->postSimple('Original', 'test', null, LedgerService::CASH, LedgerService::MEMBERS_SAVINGS, 1000);
        $reversal = $ledger->reverse($original, 'Duplicate');

        $this->expectException(\RuntimeException::class);

        $ledger->reverse($reversal, 'Reverse the reversal');
    }
}
