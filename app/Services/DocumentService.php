<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\PurchaseOrder;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\ShareAccount;
use App\Models\ShareTransaction;
use Illuminate\View\View;

class DocumentService
{
    private function authorizeMemberAccess(int $memberId): void
    {
        $user = auth()->user();
        if ($user->member_id && $user->member_id !== $memberId) {
            abort(403, 'You are not authorized to view this document.');
        }
    }

    public function savingsDepositReceipt(SavingsTransaction $transaction): View
    {
        $transaction->load(['savingsAccount.member.region']);
        $this->authorizeMemberAccess($transaction->savingsAccount->member_id);

        return view('receipts.savings-deposit', ['transaction' => $transaction]);
    }

    public function loanRepaymentReceipt(LoanRepayment $repayment): View
    {
        $repayment->load(['loan.member.region']);
        $this->authorizeMemberAccess($repayment->loan->member_id);

        return view('receipts.loan-repayment', ['repayment' => $repayment]);
    }

    public function purchaseOrderReceipt(PurchaseOrder $order): View
    {
        $order->load(['member.region', 'product', 'approvedBy']);
        $this->authorizeMemberAccess($order->member_id);

        return view('receipts.purchase-order', ['order' => $order]);
    }

    public function purchaseInvoice(PurchaseOrder $order): View
    {
        $order->load(['member.region', 'product', 'approvedBy']);
        $this->authorizeMemberAccess($order->member_id);

        return view('invoices.purchase', ['order' => $order]);
    }

    public function sharePurchaseReceipt(ShareTransaction $transaction): View
    {
        $transaction->load(['shareAccount.member.region']);
        $this->authorizeMemberAccess($transaction->shareAccount->member_id);

        return view('receipts.share-purchase', ['transaction' => $transaction]);
    }

    public function loanDisbursementReceipt(Loan $loan): View
    {
        $loan->load(['member.region', 'loanProduct']);
        $this->authorizeMemberAccess($loan->member_id);

        return view('receipts.loan-disbursement', ['loan' => $loan]);
    }

    public function loanStatement(Loan $loan): View
    {
        $loan->load([
            'member.region',
            'loanProduct',
            'approvedBy',
            'repayments',
            'schedules',
            'guarantors.member',
        ]);
        $this->authorizeMemberAccess($loan->member_id);

        return view('receipts.loan-statement', ['loan' => $loan]);
    }

    public function savingsStatement(SavingsAccount $account): View
    {
        $account->load(['member.region', 'transactions']);
        $this->authorizeMemberAccess($account->member_id);

        return view('receipts.savings-statement', ['account' => $account]);
    }

    public function shareCertificate(int $accountId): View
    {
        $account = ShareAccount::with(['member.region', 'transactions'])->findOrFail($accountId);
        $this->authorizeMemberAccess($account->member_id);

        $firstPurchase = $account->transactions()
            ->where('type', 'purchase')
            ->orderBy('transaction_date')
            ->first();

        return view('receipts.share-certificate', compact('account', 'firstPurchase'));
    }
}
