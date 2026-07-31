<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Services\LedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SavingsService
{
    private const DEFAULT_AUTO_APPROVE_LIMIT = 200000;

    /**
     * Record a deposit atomically. If auto-approve conditions are met, marks completed immediately.
     * Otherwise creates pending transaction (member portal flow).
     */
    public function recordDeposit(int $memberId, float $amount, ?string $notes = null, string $source = 'manual', ?string $evidencePath = null): SavingsTransaction
    {
        return DB::transaction(function () use ($memberId, $amount, $notes, $source, $evidencePath) {
            $account = SavingsAccount::where('member_id', $memberId)->lockForUpdate()->firstOrFail();
            $balanceBefore = $account->balance;

            $shouldAutoApprove = $this->shouldAutoApprove($memberId, $amount, $evidencePath);

            if ($shouldAutoApprove) {
                $account->increment('balance', $amount);
                $account->refresh();

                $txn = SavingsTransaction::create([
                    'savings_account_id' => $account->id,
                    'reference' => 'SAV/DEP/' . strtoupper(Str::random(8)),
                    'type' => 'deposit',
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $account->balance,
                    'source' => $source,
                    'notes' => $notes,
                    'payment_evidence_path' => $evidencePath,
                    'transaction_date' => now(),
                    'status' => 'completed',
                    'approved_by' => null,
                    'approved_at' => now(),
                ]);

                app(LedgerService::class)->postSavingsDeposit($txn->id, $amount);

                ActivityLog::create([
                    'user_id' => null,
                    'event' => 'deposit_auto_approved',
                    'description' => 'Auto-approved savings deposit #' . $txn->id . ' of ₦' . number_format($amount, 2),
                    'ip_address' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                    'properties' => [
                        'amount' => $amount,
                        'savings_account_id' => $account->id,
                        'source' => $source,
                    ],
                ]);

                return $txn;
            }

            return SavingsTransaction::create([
                'savings_account_id' => $account->id,
                'reference' => 'SAV/DEP/' . strtoupper(Str::random(8)),
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore,
                'source' => $source,
                'notes' => $notes,
                'payment_evidence_path' => $evidencePath,
                'transaction_date' => now(),
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Determine if a deposit should be auto-approved based on rules.
     */
    private function shouldAutoApprove(int $memberId, float $amount, ?string $evidencePath): bool
    {
        $limit = (float) (Company::instance()->auto_approve_deposit_limit ?? self::DEFAULT_AUTO_APPROVE_LIMIT);

        if ($amount > $limit) {
            return false;
        }

        if (!$evidencePath) {
            return false;
        }

        $member = Member::find($memberId);
        if (!$member) {
            return false;
        }

        if ($member->is_fraud_flagged) {
            return false;
        }

        return $member->status === 'active';
    }

    /**
     * Record a withdrawal request (pending approval).
     */
    public function recordWithdrawalRequest(int $memberId, float $amount, ?string $notes = null, string $source = 'manual'): SavingsTransaction
    {
        $account = SavingsAccount::where('member_id', $memberId)->lockForUpdate()->firstOrFail();
        $balanceBefore = $account->balance;

        return SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'reference' => 'SAV/WTH/' . strtoupper(Str::random(8)),
            'type' => 'withdrawal',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore,
            'source' => $source,
            'notes' => $notes,
            'transaction_date' => now(),
            'status' => 'pending',
        ]);
    }

    /**
     * Calculate a member's health score.
     * Formula: (savings_balance / (active_loan_outstanding + 1)) * 100
     */
    public function calculateHealthScore(Member $member): float
    {
        $savingsBalance = $member->savingsAccount?->balance ?? 0;
        $outstandingLoans = $member->loans()
            ->whereIn('status', ['disbursed', 'repaying', 'arrears'])
            ->sum('outstanding');

        return round($savingsBalance / max($outstandingLoans, 1) * 100, 1);
    }

    /**
     * Approve a pending withdrawal atomically.
     */
    public function approveWithdrawal(SavingsTransaction $transaction): SavingsTransaction
    {
        return DB::transaction(function () use ($transaction) {
            $account = SavingsAccount::where('id', $transaction->savings_account_id)->lockForUpdate()->firstOrFail();

            if ($transaction->amount > $account->balance) {
                throw new \RuntimeException('Insufficient balance for withdrawal.');
            }

            $account->decrement('balance', $transaction->amount);
            $account->refresh();

            $transaction->update([
                'status' => 'completed',
                'balance_after' => $account->balance,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            app(LedgerService::class)->postSavingsWithdrawal($transaction->id, $transaction->amount);

            return $transaction->fresh();
        });
    }

    /**
     * Approve a pending deposit atomically.
     */
    public function approveDeposit(SavingsTransaction $transaction): SavingsTransaction
    {
        return DB::transaction(function () use ($transaction) {
            $account = SavingsAccount::where('id', $transaction->savings_account_id)->lockForUpdate()->firstOrFail();

            $account->increment('balance', $transaction->amount);
            $account->refresh();

            $transaction->update([
                'status' => 'completed',
                'balance_after' => $account->balance,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            app(LedgerService::class)->postSavingsDeposit($transaction->id, $transaction->amount);

            return $transaction->fresh();
        });
    }
}
