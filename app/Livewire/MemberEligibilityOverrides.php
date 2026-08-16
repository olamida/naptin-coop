<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\MemberLoanEligibilityOverride;
use App\Models\User;
use App\Services\LoanEligibilityService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MemberEligibilityOverrides extends Component
{
    use WithPagination;

    public Member $member;

    public $showModal = false;

    public $editingOverride = null;

    public $loanProductId = '';

    public $customMultiplier = '';

    public $customMaxDeductionPercent = '';

    public $customMaxAmount = '';

    public $reasonCategory = '';

    public $reasonDetails = '';

    public $secondApprovedBy = '';

    public $validFrom = '';

    public $validUntil = '';

    public $isActive = true;

    protected $listeners = ['refreshOverrides' => '$refresh'];

    protected function rules(): array
    {
        $rules = [
            'loanProductId' => 'required|exists:loan_products,id',
            'customMultiplier' => 'nullable|numeric|min:0.1|max:10',
            'customMaxDeductionPercent' => 'nullable|numeric|min:0|max:66.67',
            'customMaxAmount' => 'nullable|numeric|min:1',
            'reasonCategory' => 'required|in:retirement_recovery,defaulter_catchup,long_service_goodwill,emergency_medical,exco_discretion,agm_approval,other',
            'reasonDetails' => 'required|string|min:20|max:1000',
            'secondApprovedBy' => 'nullable|exists:users,id',
            'validFrom' => 'required|date',
            'validUntil' => 'nullable|date|after_or_equal:validFrom',
            'isActive' => 'boolean',
        ];

        // Require second approval if deduction > 50%
        if ($this->customMaxDeductionPercent && (float) $this->customMaxDeductionPercent > 50) {
            $rules['secondApprovedBy'] = 'required|exists:users,id';
        }

        return $rules;
    }

    public function mount(Member $member): void
    {
        $this->member = $member->load(['savingsAccount', 'loans.loanProduct']);
        $this->validFrom = now()->toDateString();
    }

    public function openModal(?MemberLoanEligibilityOverride $override = null): void
    {
        $this->resetValidation();
        $this->resetForm();

        if ($override) {
            $this->editingOverride = $override;
            $this->loanProductId = $override->loan_product_id;
            $this->customMultiplier = $override->custom_multiplier;
            $this->customMaxDeductionPercent = $override->custom_max_deduction_percent;
            $this->customMaxAmount = $override->custom_max_amount;
            $this->reasonCategory = $override->reason_category;
            $this->reasonDetails = $override->reason_details;
            $this->secondApprovedBy = $override->second_approved_by;
            $this->validFrom = $override->valid_from?->toDateString();
            $this->validUntil = $override->valid_until?->toDateString();
            $this->isActive = $override->is_active;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingOverride = null;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->loanProductId = '';
        $this->customMultiplier = '';
        $this->customMaxDeductionPercent = '';
        $this->customMaxAmount = '';
        $this->reasonCategory = '';
        $this->reasonDetails = '';
        $this->secondApprovedBy = '';
        $this->validFrom = now()->toDateString();
        $this->validUntil = '';
        $this->isActive = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'member_id' => $this->member->id,
            'loan_product_id' => $this->loanProductId,
            'custom_multiplier' => $this->customMultiplier ?: null,
            'custom_max_deduction_percent' => $this->customMaxDeductionPercent ?: null,
            'custom_max_amount' => $this->customMaxAmount ?: null,
            'reason_category' => $this->reasonCategory,
            'reason_details' => $this->reasonDetails,
            'approved_by' => Auth::id(),
            'second_approved_by' => $this->secondApprovedBy ?: null,
            'valid_from' => $this->validFrom,
            'valid_until' => $this->validUntil ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingOverride) {
            $this->editingOverride->update($data);
            $message = 'Override updated successfully.';
        } else {
            MemberLoanEligibilityOverride::create($data);
            $message = 'Override created successfully.';
        }

        // Log activity
        ActivityLog::log(
            $this->editingOverride ? 'member_loan_override_updated' : 'member_loan_override_created',
            $this->editingOverride ? 'Member loan eligibility override updated' : 'Member loan eligibility override created',
            $data
        );

        // Notify member and auditor
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message,
        ]);

        $this->closeModal();
        $this->dispatch('refreshOverrides');
    }

    public function deactivate(MemberLoanEligibilityOverride $override): void
    {
        $override->update(['is_active' => false]);

        ActivityLog::log(
            'member_loan_override_deactivated',
            'Member loan eligibility override deactivated',
            ['override_id' => $override->id]
        );

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Override deactivated successfully.',
        ]);

        $this->dispatch('refreshOverrides');
    }

    public function getOverridesProperty()
    {
        return MemberLoanEligibilityOverride::where('member_id', $this->member->id)
            ->with('loanProduct', 'approvedBy', 'secondApprovedBy')
            ->latest('created_at')
            ->paginate(10);
    }

    public function getEligibilityProperty()
    {
        $service = app(LoanEligibilityService::class);
        $products = LoanProduct::where('enabled', true)->get();

        $eligibility = [];
        foreach ($products as $product) {
            $eligibility[$product->id] = $service->calculateMaxEligibleAmount($this->member, $product);
        }

        return $eligibility;
    }

    public function getDeductionAnalysisProperty()
    {
        $service = app(LoanEligibilityService::class);

        return $service->calculateTotalDeductionsPercent($this->member);
    }

    public function render()
    {
        $products = LoanProduct::where('enabled', true)->get();
        $users = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin', 'president', 'auditor', 'treasurer']))->get();
        $reasonCategories = config('cooperative.override_reason_categories', []);

        return view('livewire.member-eligibility-overrides', [
            'products' => $products,
            'users' => $users,
            'reasonCategories' => $reasonCategories,
        ]);
    }
}
