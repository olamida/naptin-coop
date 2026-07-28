<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\PurchaseOrder;
use App\Models\ShareTransaction;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    private function authorizeMemberAccess($memberId): void
    {
        $user = auth()->user();
        if ($user->member_id && $user->member_id !== $memberId) {
            abort(403, 'You are not authorized to view this receipt.');
        }
    }

    public function savingsDeposit(SavingsTransaction $transaction): \Illuminate\View\View
    {
        $transaction->load(['savingsAccount.member.region']);
        $this->authorizeMemberAccess($transaction->savingsAccount->member_id);

        return view('receipts.savings-deposit', ['transaction' => $transaction]);
    }

    public function loanRepayment(LoanRepayment $repayment): \Illuminate\View\View
    {
        $repayment->load(['loan.member.region']);
        $this->authorizeMemberAccess($repayment->loan->member_id);

        return view('receipts.loan-repayment', ['repayment' => $repayment]);
    }

    public function purchaseOrder(PurchaseOrder $order): \Illuminate\View\View
    {
        $order->load(['member.region', 'product', 'approvedBy']);
        $this->authorizeMemberAccess($order->member_id);

        return view('receipts.purchase-order', ['order' => $order]);
    }

    public function sharePurchase(ShareTransaction $transaction): \Illuminate\View\View
    {
        $transaction->load(['shareAccount.member.region']);
        $this->authorizeMemberAccess($transaction->shareAccount->member_id);

        return view('receipts.share-purchase', ['transaction' => $transaction]);
    }

    public function loanDisbursement(Loan $loan): \Illuminate\View\View
    {
        $loan->load(['member.region', 'loanProduct']);
        $this->authorizeMemberAccess($loan->member_id);

        return view('receipts.loan-disbursement', ['loan' => $loan]);
    }

    public function loanStatement(Loan $loan): \Illuminate\View\View
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

    public function savingsStatement(SavingsAccount $account): \Illuminate\View\View
    {
        $account->load([
            'member.region',
            'transactions',
        ]);
        $this->authorizeMemberAccess($account->member_id);

        return view('receipts.savings-statement', ['account' => $account]);
    }

    public function shareCertificate($account): \Illuminate\View\View
    {
        $account = \App\Models\ShareAccount::with([
            'member.region',
            'transactions',
        ])->findOrFail($account);
        $this->authorizeMemberAccess($account->member_id);

        $firstPurchase = $account->transactions()
            ->where('type', 'purchase')
            ->orderBy('transaction_date')
            ->first();

        return view('receipts.share-certificate', [
            'account' => $account,
            'firstPurchase' => $firstPurchase,
        ]);
    }
}
