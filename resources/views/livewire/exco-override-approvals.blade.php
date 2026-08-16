<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">EXCO Override Approvals</h1>
            <p class="text-slate-500 mt-1">Review and approve loan eligibility overrides</p>
        </div>
    </div>

    <!-- Pending Approvals Table -->
    <div class="bg-white rounded-[10px] border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Pending Approvals ({{ $pendingApprovals->total() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Member</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Loan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Applied Multiplier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Projected Deduction %</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Override Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Requested By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Days Waiting</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($pendingApprovals as $loan)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $loan->member->full_name }}</div>
                                <div class="text-sm text-slate-500">{{ $loan->member->staff_id_display }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $loan->loan_number }}</div>
                                <div class="text-sm text-slate-500">{{ $loan->type }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $loan->loanProduct->name }}</td>
                            <td class="px-4 py-3">₦{{ number_format($loan->amount, 2) }}</td>
                            <td class="px-4 py-3">
                                @if($loan->is_multiplier_override)
                                    <span class="font-medium text-emerald-600">{{ $loan->applied_multiplier }}x</span>
                                    <span class="ml-1 text-sm text-slate-400">(Default: {{ $loan->loanProduct->default_multiplier }}x)</span>
                                @else
                                    <span class="text-slate-600">{{ $loan->loanProduct->default_multiplier }}x</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($loan->total_deduction_percent_at_approval)
                                    <span class="font-medium
                                        {{ $loan->total_deduction_percent_at_approval > 50 ? 'text-rose-600' : ($loan->total_deduction_percent_at_approval > 33.33 ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ $loan->total_deduction_percent_at_approval }}%
                                    </span>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    @if($loan->is_multiplier_override)
                                        <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">Multiplier</span>
                                    @endif
                                    @if($loan->is_deduction_cap_override)
                                        <span class="px-2 py-0.5 text-xs bg-amber-100 text-amber-700 rounded-full">Deduction Cap</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $loan->approvalLogs->first()?->user?->name ?? 'System' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-500">
                                {{ $loan->created_at->diffInDays(now()) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button wire:click="viewDetails({{ $loan->id }})"
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">View</button>
                                    @if($loan->is_multiplier_override && !$loan->is_deduction_cap_override)
                                        <button wire:click="openApproveModal({{ $loan->id }}, 'multiplier')"
                                            class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">Approve Multiplier</button>
                                    @elseif(!$loan->is_multiplier_override && $loan->is_deduction_cap_override)
                                        <button wire:click="openApproveModal({{ $loan->id }}, 'deduction')"
                                            class="text-sm text-amber-600 hover:text-amber-800 font-medium">Approve Deduction</button>
                                    @elseif($loan->is_multiplier_override && $loan->is_deduction_cap_override)
                                        <button wire:click="openApproveModal({{ $loan->id }}, 'both')"
                                            class="text-sm text-purple-600 hover:text-purple-800 font-medium">Approve Both</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-500">
                                No pending override approvals.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pendingApprovals->links() }}
    </div>

    <!-- Details Modal -->
    @if($showDetails && $selectedLoan)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeDetails"></div>
                <div class="relative bg-white rounded-[16px] w-full max-w-4xl shadow-xl max-h-[90vh] overflow-y-auto" wire:keydown.escape="closeDetails">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between sticky top-0 bg-white z-10 rounded-t-[16px]">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Loan Details: {{ $selectedLoan->loan_number }}
                        </h3>
                        <button wire:click="closeDetails" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Member Info -->
                        <div class="bg-slate-50 rounded-[10px] p-4">
                            <h4 class="font-medium text-slate-900 mb-3">Member Information</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <p class="text-slate-500">Name</p>
                                    <p class="font-medium">{{ $selectedLoan->member->full_name }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Staff ID</p>
                                    <p class="font-medium">{{ $selectedLoan->member->staff_id_display }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Savings Balance</p>
                                    <p class="font-medium">₦{{ number_format($selectedLoan->member->savingsAccount?->balance ?? 0, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Net Salary</p>
                                    <p class="font-medium">₦{{ number_format($selectedLoan->member->monthly_net_salary ?? $selectedLoan->member->monthly_salary ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Details -->
                        <div class="bg-slate-50 rounded-[10px] p-4">
                            <h4 class="font-medium text-slate-900 mb-3">Loan Details</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <p class="text-slate-500">Product</p>
                                    <p class="font-medium">{{ $selectedLoan->loanProduct->name }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Amount</p>
                                    <p class="font-medium">₦{{ number_format($selectedLoan->amount, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Tenure</p>
                                    <p class="font-medium">{{ $selectedLoan->tenure_months }} months</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Interest Rate</p>
                                    <p class="font-medium">{{ $selectedLoan->interest_rate }}%</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Monthly Repayment</p>
                                    <p class="font-medium">₦{{ number_format($selectedLoan->monthly_repayment, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Default Multiplier</p>
                                    <p class="font-medium">{{ $selectedLoan->loanProduct->default_multiplier }}x</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Applied Multiplier</p>
                                    <p class="font-medium {{ $selectedLoan->is_multiplier_override ? 'text-emerald-600' : '' }}">
                                        {{ $selectedLoan->applied_multiplier ?? $selectedLoan->loanProduct->default_multiplier }}x
                                    </p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Projected Deduction %</p>
                                    <p class="font-medium
                                        {{ ($selectedLoan->total_deduction_percent_at_approval ?? 0) > 50 ? 'text-rose-600' : (($selectedLoan->total_deduction_percent_at_approval ?? 0) > 33.33 ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ $selectedLoan->total_deduction_percent_at_approval ?? 'N/A' }}%
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Override Details -->
                        @if($selectedLoan->is_multiplier_override || $selectedLoan->is_deduction_cap_override)
                            <div class="bg-amber-50 border border-amber-200 rounded-[10px] p-4">
                                <h4 class="font-medium text-amber-900 mb-3">Override Details</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    @if($selectedLoan->is_multiplier_override)
                                        <div>
                                            <p class="text-amber-700">Multiplier Override</p>
                                            <p class="font-medium">Applied: {{ $selectedLoan->applied_multiplier }}x (Default: {{ $selectedLoan->loanProduct->default_multiplier }}x)</p>
                                            @if($selectedLoan->multiplierOverride)
                                                <p class="text-amber-600">Reason: {{ ucfirst(str_replace('_', ' ', $selectedLoan->multiplierOverride->reason_category)) }}</p>
                                                <p class="text-amber-600">Details: {{ $selectedLoan->multiplierOverride->reason_details }}</p>
                                            @endif
                                        </div>
                                    @endif
                                    @if($selectedLoan->is_deduction_cap_override)
                                        <div>
                                            <p class="text-amber-700">Deduction Cap Override</p>
                                            <p class="font-medium">Projected: {{ $selectedLoan->total_deduction_percent_at_approval }}% (Default: 33.33%)</p>
                                            <p class="text-amber-600">Reason: {{ $selectedLoan->deduction_override_reason }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Eligibility Analysis -->
                        @php
                            $service = app(\App\Services\LoanEligibilityService::class);
                            $eligibility = $service->calculateMaxEligibleAmount($selectedLoan->member, $selectedLoan->loanProduct);
                            $deductionAnalysis = $service->calculateTotalDeductionsPercent($selectedLoan->member, $selectedLoan->monthly_repayment);
                        @endphp

                        <div class="bg-blue-50 border border-blue-200 rounded-[10px] p-4">
                            <h4 class="font-medium text-blue-900 mb-3">Eligibility Analysis</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-blue-700">Max Eligible (Current Policy)</p>
                                    <p class="font-medium text-lg">₦{{ number_format($eligibility['max_eligible'], 2) }}</p>
                                    <p class="text-blue-600">{{ $eligibility['formula'] }}</p>
                                </div>
                                <div>
                                    <p class="text-blue-700">Deduction Analysis</p>
                                    <p class="font-medium">Current: {{ $deductionAnalysis['current_percent'] }}%</p>
                                    <p class="font-medium">With This Loan: {{ $deductionAnalysis['projected_percent'] }}%</p>
                                    <p class="text-blue-600">Default Cap: {{ $deductionAnalysis['default_cap'] }}% | Applied Cap: {{ $deductionAnalysis['applied_cap'] }}% | Hard Cap: {{ $deductionAnalysis['hard_cap'] }}%</p>
                                </div>
                            </div>
                        </div>

                        <!-- Guarantors -->
                        @if($selectedLoan->guarantors->count())
                            <div class="bg-slate-50 rounded-[10px] p-4">
                                <h4 class="font-medium text-slate-900 mb-3">Guarantors</h4>
                                <div class="space-y-2">
                                    @foreach($selectedLoan->guarantors as $guarantor)
                                        <div class="flex items-center justify-between p-2 bg-white rounded-[8px]">
                                            <div>
                                                <p class="font-medium">{{ $guarantor->member->full_name }}</p>
                                                <p class="text-sm text-slate-500">{{ $guarantor->member->staff_id_display }}</p>
                                            </div>
                                            <span class="px-3 py-1 text-xs rounded-full
                                                @switch($guarantor->status->value)
                                                    @case('accepted') bg-emerald-100 text-emerald-700 @break
                                                    @case('declined') bg-rose-100 text-rose-700 @break
                                                    @default bg-amber-100 text-amber-700
                                                @endswitch
                                            ">
                                                {{ ucfirst($guarantor->status->value) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Approval History -->
                        @if($selectedLoan->approvalLogs->count())
                            <div class="bg-slate-50 rounded-[10px] p-4">
                                <h4 class="font-medium text-slate-900 mb-3">Approval History</h4>
                                <div class="space-y-2">
                                    @foreach($selectedLoan->approvalLogs as $log)
                                        <div class="flex items-center gap-3 p-2 bg-white rounded-[8px]">
                                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-sm font-medium">
                                                {{ $log->user?->initials ?? 'SY' }}
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</p>
                                                <p class="text-sm text-slate-500">{{ $log->user?->name ?? 'System' }} • {{ $log->created_at->format('d M Y H:i') }}</p>
                                                @if($log->notes)
                                                    <p class="text-sm text-slate-600">{{ $log->notes }}</p>
                                                @endif
                                            </div>
                                            @if($log->old_status && $log->new_status)
                                                <div class="text-sm text-slate-500">
                                                    {{ $log->old_status }} → {{ $log->new_status }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Approve Modal -->
    @if($showApproveModal && $selectedLoan)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeApproveModal"></div>
                <div class="relative bg-white rounded-[16px] w-full max-w-md shadow-xl" wire:keydown.escape="closeApproveModal">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Approve Override for {{ $selectedLoan->loan_number }}
                        </h3>
                        <button wire:click="closeApproveModal" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="approve" class="p-6 space-y-4">
                        <div class="bg-amber-50 border border-amber-200 rounded-[8px] p-4">
                            <h4 class="font-medium text-amber-900 mb-2">Override Type</h4>
                            <div class="text-sm text-amber-700 space-y-1">
                                @if($approvalType === 'multiplier' || $approvalType === 'both')
                                    <p>• Multiplier: {{ $selectedLoan->applied_multiplier }}x (Default: {{ $selectedLoan->loanProduct->default_multiplier }}x)</p>
                                @endif
                                @if($approvalType === 'deduction' || $approvalType === 'both')
                                    <p>• Deduction Cap: {{ $selectedLoan->total_deduction_percent_at_approval }}% (Default: 33.33%)</p>
                                @endif
                            </div>
                        </div>

                        @if($secondApproval)
                            <div class="bg-rose-50 border border-rose-200 rounded-[8px] p-4">
                                <h4 class="font-medium text-rose-900 mb-2">⚠ Second Approval Required</h4>
                                <p class="text-sm text-rose-700">Deduction exceeds 50%. This approval will count as both first and second approval.</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Approval Reason <span class="text-rose-500">*</span></label>
                            <textarea wire:model="approvalReason" rows="3"
                                class="w-full border border-slate-300 rounded-[8px] px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter approval reason (minimum 10 characters)"></textarea>
                            @error('approvalReason') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                            <button type="button" wire:click="closeApproveModal" class="btn-secondary">Cancel</button>
                            <button type="button" wire:click="reject" class="btn-danger">Reject</button>
                            <button type="submit" class="btn-primary">Approve</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>