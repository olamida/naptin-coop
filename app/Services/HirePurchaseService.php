<?php

namespace App\Services;

use App\Models\HirePurchaseSchedule;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class HirePurchaseService
{
    /**
     * Generate a loan-like repayment schedule for a hire-purchase order.
     * The monthly repayment is fixed (flat principal); the final instalment
     * absorbs rounding drift so the schedule sums exactly to the total.
     */
    public function generateSchedule(PurchaseOrder $order): void
    {
        if ($order->payment_type !== 'hire_purchase') {
            return;
        }

        $total = round((float) $order->total_amount, 2);
        $monthly = round((float) $order->monthly_repayment, 2);
        $tenure = $monthly > 0 ? max(1, (int) ceil($total / $monthly)) : 1;

        $balance = $total;
        $rows = [];
        for ($i = 1; $i <= $tenure; $i++) {
            $principal = $monthly > 0 ? $monthly : $total;
            if ($i === $tenure) {
                $principal = round($total - ($tenure - 1) * ($monthly > 0 ? $monthly : 0), 2);
            }

            $balance = round($balance - $principal, 2);

            $rows[] = [
                'purchase_order_id' => $order->id,
                'installment_number' => $i,
                'due_date' => now()->addMonths($i)->startOfMonth()->toDateString(),
                'principal_amount' => $principal,
                'total_amount' => $principal,
                'balance_after' => $balance,
                'status' => 'pending',
                'amount_paid' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        HirePurchaseSchedule::where('purchase_order_id', $order->id)->delete();
        HirePurchaseSchedule::insert($rows);
    }

    /**
     * Apply a payment across the unpaid instalments of an order (in order).
     * Each fully-covered instalment posts a cash journal against Purchase
     * Receivables (1201). When every instalment is paid the order completes.
     *
     * @throws \RuntimeException when the payment exceeds the outstanding balance
     */
    public function applyPayment(PurchaseOrder $order, float $amount, ?string $paymentDate = null): HirePurchaseSchedule
    {
        $paymentDate = $paymentDate ?? now()->toDateString();
        $remaining = round($amount, 2);

        if ($remaining <= 0) {
            throw new \RuntimeException('Payment amount must be greater than zero.');
        }

        $ledger = app(LedgerService::class);

        return DB::transaction(function () use ($order, $remaining, $paymentDate, $ledger) {
            $schedules = $order->schedules()
                ->where('status', '!=', 'paid')
                ->orderBy('installment_number')
                ->lockForUpdate()
                ->get();

            $outstanding = $schedules->sum(function (HirePurchaseSchedule $schedule) {
                return round((float) $schedule->principal_amount - (float) $schedule->amount_paid, 2);
            });

            if (round($remaining, 2) > round($outstanding, 2)) {
                throw new \RuntimeException('Payment exceeds the outstanding balance (₦'.number_format($outstanding, 2).').');
            }

            $paidAmount = 0;
            $lastPaid = null;

            foreach ($schedules as $schedule) {
                if ($remaining <= 0) {
                    break;
                }

                $owed = round((float) $schedule->principal_amount - (float) $schedule->amount_paid, 2);
                if ($owed <= 0) {
                    continue;
                }

                $apply = min($remaining, $owed);
                $newPaid = round((float) $schedule->amount_paid + $apply, 2);
                $fullyPaid = $newPaid >= (float) $schedule->principal_amount;

                $schedule->update([
                    'amount_paid' => $newPaid,
                    'status' => $fullyPaid ? 'paid' : $schedule->status,
                    'paid_at' => $fullyPaid ? $paymentDate : $schedule->paid_at,
                ]);

                if ($fullyPaid) {
                    $ledger->postHirePurchaseInstalment($order->id, $schedule->id, $apply);
                    $paidAmount = round($paidAmount + $apply, 2);
                    $lastPaid = $schedule;
                }

                $remaining = round($remaining - $apply, 2);
            }

            $order->increment('amount_paid', $paidAmount);

            if (! $order->schedules()->where('status', '!=', 'paid')->exists()) {
                $order->update(['status' => 'completed']);
            }

            if ($lastPaid) {
                return $lastPaid->fresh();
            }

            return $order->schedules()->orderBy('installment_number')->first();
        });
    }
}
