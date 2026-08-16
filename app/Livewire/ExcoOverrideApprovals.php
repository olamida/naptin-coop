<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Loan;
use App\Models\MemberLoanEligibilityOverride;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ExcoOverrideApprovals extends Component
{
    use WithPagination;

    public $showDetails = false;

    public $selectedLoan = null;

    public $showApproveModal = false;

    public $approvalType = ''; // 'multiplier' or 'deduction' or 'both'

    public $approvalReason = '';

    public $secondApproval = false;

    protected $listeners = ['refreshApprovals' => '$refresh'];

    public function mount(): void
    {
        //
    }

    public function viewDetails(Loan $loan): void
    {
        $this->selectedLoan = $loan->load(['member.savingsAccount', 'member.loans.loanProduct', 'loanProduct', 'guarantors.member']);
        $this->showDetails = true;
    }

    public function closeDetails(): void
    {
        $this->showDetails = false;
        $this->selectedLoan = null;
    }

    public function openApproveModal(Loan $loan, string $type): void
    {
        $this->selectedLoan = $loan->load(['member', 'loanProduct']);
        $this->approvalType = $type;
        $this->showApproveModal = true;
        $this->approvalReason = '';
        $this->secondApproval = $type === 'deduction' && ($loan->total_deduction_percent_at_approval ?? 0) > 50;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
        $this->selectedLoan = null;
        $this->approvalType = '';
        $this->approvalReason = '';
        $this->secondApproval = false;
    }

    public function approve(): void
    {
        $this->validate([
            'approvalReason' => 'required|string|min:10|max:500',
        ]);

        $loan = $this->selectedLoan;

        if ($this->approvalType === 'multiplier' || $this->approvalType === 'both') {
            // Create or update override
            $override = MemberLoanEligibilityOverride::updateOrCreate(
                [
                    'member_id' => $loan->member_id,
                    'loan_product_id' => $loan->loan_product_id,
                    'is_active' => true,
                ],
                [
                    'custom_multiplier' => $loan->applied_multiplier,
                    'custom_max_deduction_percent' => $loan->is_deduction_cap_override ? $loan->total_deduction_percent_at_approval : null,
                    'reason_category' => 'exco_discretion',
                    'reason_details' => $this->approvalReason,
                    'approved_by' => Auth::id(),
                    'second_approved_by' => $this->secondApproval ? Auth::id() : null,
                    'valid_from' => now()->toDateString(),
                    'is_active' => true,
                ]
            );

            $loan->update([
                'is_multiplier_override' => true,
                'multiplier_override_id' => $override->id,
                'approved_multiplier' => $loan->applied_multiplier,
            ]);
        }

        if ($this->approvalType === 'deduction' || $this->approvalType === 'both') {
            $loan->update([
                'is_deduction_cap_override' => true,
                'deduction_override_reason' => $this->approvalReason,
                'deduction_override_approved_by' => Auth::id(),
            ]);
        }

        // Log activity
        ActivityLog::log(
            'loan_override_approved',
            "Loan override approved: {$this->approvalType} for {$loan->loan_number}",
            [
                'loan_id' => $loan->id,
                'approval_type' => $this->approvalType,
                'reason' => $this->approvalReason,
                'second_approval' => $this->secondApproval,
            ]
        );

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Override approved successfully.',
        ]);

        $this->closeApproveModal();
        $this->dispatch('refreshApprovals');
    }

    public function reject(): void
    {
        $this->validate([
            'approvalReason' => 'required|string|min:10|max:500',
        ]);

        $loan = $this->selectedLoan;

        $loan->update([
            'status' => 'rejected',
            'rejection_reason' => "Override rejected: {$this->approvalReason}",
        ]);

        // Log activity
        ActivityLog::log(
            'loan_override_rejected',
            "Loan override rejected: {$this->approvalType} for {$loan->loan_number}",
            [
                'loan_id' => $loan->id,
                'approval_type' => $this->approvalType,
                'reason' => $this->approvalReason,
            ]
        );

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Override rejected and loan rejected.',
        ]);

        $this->closeApproveModal();
        $this->dispatch('refreshApprovals');
    }

    public function getPendingApprovalsProperty()
    {
        return Loan::whereIn('status', ['pending_override_approval', 'pending'])
            ->where(function ($q) {
                $q->where('is_multiplier_override', true)
                    ->orWhere('is_deduction_cap_override', true)
                    ->orWhereHas('approvalLogs', fn ($q) => $q->whereIn('action', ['multiplier_override_requested', 'deduction_override_requested']));
            })
            ->with(['member', 'loanProduct', 'approvalLogs.user'])
            ->latest('created_at')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.exco-override-approvals', [
            'pendingApprovals' => $this->pendingApprovals,
        ]);
    }
}
