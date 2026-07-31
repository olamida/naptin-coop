<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\PurchaseOrder;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\ShareAccount;
use App\Models\ShareTransaction;
use App\Services\DocumentService;

class ReceiptController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService,
    ) {}

    public function savingsDeposit(SavingsTransaction $transaction)
    {
        return $this->documentService->savingsDepositReceipt($transaction);
    }

    public function loanRepayment(LoanRepayment $repayment)
    {
        return $this->documentService->loanRepaymentReceipt($repayment);
    }

    public function purchaseOrder(PurchaseOrder $order)
    {
        return $this->documentService->purchaseOrderReceipt($order);
    }

    public function sharePurchase(ShareTransaction $transaction)
    {
        return $this->documentService->sharePurchaseReceipt($transaction);
    }

    public function loanDisbursement(Loan $loan)
    {
        return $this->documentService->loanDisbursementReceipt($loan);
    }

    public function loanStatement(Loan $loan)
    {
        return $this->documentService->loanStatement($loan);
    }

    public function savingsStatement(SavingsAccount $account)
    {
        return $this->documentService->savingsStatement($account);
    }

    public function shareCertificate($account)
    {
        return $this->documentService->shareCertificate((int) $account);
    }
}
