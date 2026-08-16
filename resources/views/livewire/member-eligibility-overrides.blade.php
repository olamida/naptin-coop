<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Eligibility Overrides</h1>
            <p class="text-slate-500 mt-1">
                Member: {{ $member->full_name }} ({{ $member->staff_id_display }})
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="openModal" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Override
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-[10px] p-4 border border-slate-200">
            <p class="text-sm text-slate-500">Savings Balance</p>
            <p class="text-2xl font-bold text-slate-900">₦{{ number_format($member->savingsAccount?->balance ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-[10px] p-4 border border-slate-200">
            <p class="text-sm text-slate-500">Net Salary</p>
            <p class="text-2xl font-bold text-slate-900">₦{{ number_format($member->monthly_net_salary ?? $member->monthly_salary ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-[10px] p-4 border border-slate-200">
            <p class="text-sm text-slate-500">Current Deduction %</p>
            <p class="text-2xl font-bold {{ $deductionAnalysis['is_within_default'] ? 'text-emerald-600' : ($deductionAnalysis['is_exceeds_hard'] ? 'text-rose-600' : 'text-amber-600') }}">
                {{ $deductionAnalysis['current_percent'] }}%
            </p>
        </div>
        <div class="bg-white rounded-[10px] p-4 border border-slate-200">
            <p class="text-sm text-slate-500">Applied Cap</p>
            <p class="text-2xl font-bold text-slate-900">{{ $deductionAnalysis['applied_cap'] }}%</p>
        </div>
    </div>

    <!-- Eligibility Summary -->
    <div class="bg-white rounded-[10px] border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900">Loan Product Eligibility</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Default Multiplier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Applied Multiplier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Max Eligible</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Override Active</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Formula</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($products as $product)
                        @php
                            $elig = $eligibility[$product->id] ?? [];
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $product->name }}</td>
                            <td class="px-4 py-3">{{ $elig['default_multiplier'] ?? $product->default_multiplier }}x</td>
                            <td class="px-4 py-3 font-medium {{ $elig['is_override'] ? 'text-emerald-600' : '' }}">
                                {{ $elig['applied_multiplier'] ?? $product->default_multiplier }}x
                                @if($elig['is_override'])
                                    <span class="ml-2 px-2 py-0.5 text-xs bg-emerald-100 text-emerald-700 rounded-full">Override</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">₦{{ number_format($elig['max_eligible'] ?? 0, 2) }}</td>
                            <td class="px-4 py-3">
                                @if($elig['is_override'])
                                    <span class="px-2 py-0.5 text-xs bg-emerald-100 text-emerald-700 rounded-full">Yes</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs bg-slate-100 text-slate-600 rounded-full">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $elig['formula'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Overrides Table -->
    <div class="bg-white rounded-[10px] border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Member Overrides</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Custom Multiplier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Custom Deduction %</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Valid Period</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Approved By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($overrides as $override)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $override->loanProduct->name }}</td>
                            <td class="px-4 py-3">
                                @if($override->custom_multiplier)
                                    <span class="font-medium text-emerald-600">{{ $override->custom_multiplier }}x</span>
                                    <span class="ml-1 text-sm text-slate-400">(Default: {{ $override->loanProduct->default_multiplier }}x)</span>
                                @else
                                    <span class="text-slate-400">Default</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($override->custom_max_deduction_percent)
                                    <span class="font-medium {{ $override->custom_max_deduction_percent > 50 ? 'text-rose-600' : 'text-amber-600' }}">
                                        {{ $override->custom_max_deduction_percent }}%
                                    </span>
                                    <span class="ml-1 text-sm text-slate-400">(Default: 33.33%)</span>
                                @else
                                    <span class="text-slate-400">Default</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs rounded-full
                                    @switch($override->reason_category)
                                        @case('retirement_recovery') bg-blue-100 text-blue-700 @break
                                        @case('defaulter_catchup') bg-rose-100 text-rose-700 @break
                                        @case('long_service_goodwill') bg-emerald-100 text-emerald-700 @break
                                        @case('emergency_medical') bg-red-100 text-red-700 @break
                                        @case('exco_discretion') bg-purple-100 text-purple-700 @break
                                        @case('agm_approval') bg-amber-100 text-amber-700 @break
                                        @default bg-slate-100 text-slate-700
                                    @endswitch
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $override->reason_category)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $override->valid_from->format('d M Y') }}
                                @if($override->valid_until)
                                    to {{ $override->valid_until->format('d M Y') }}
                                @else
                                    <span class="text-slate-400 ml-1">(ongoing)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $override->approvedBy->name ?? 'Unknown' }}
                                @if($override->second_approved_by)
                                    <br><span class="text-xs text-slate-500">2nd: {{ $override->secondApprovedBy->name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($override->is_active && (!$override->valid_until || $override->valid_until->isFuture()))
                                    <span class="px-2 py-0.5 text-xs bg-emerald-100 text-emerald-700 rounded-full">Active</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs bg-slate-100 text-slate-600 rounded-full">Expired</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button wire:click="openModal({{ $override->id }})"
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                    @if($override->is_active)
                                        <button wire:click="deactivate({{ $override->id }})"
                                            class="text-sm text-rose-600 hover:text-rose-800 font-medium"
                                            onclick="return confirm('Deactivate this override?')">Deactivate</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                No overrides configured for this member.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $overrides->links() }}
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
                <div class="relative bg-white rounded-[16px] w-full max-w-2xl shadow-xl" wire:keydown.escape="closeModal">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ $editingOverride ? 'Edit Override' : 'Add Eligibility Override' }}
                        </h3>
                        <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="p-6 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Loan Product <span class="text-rose-500">*</span></label>
                            <select wire:model="loanProductId" class="w-full border border-slate-300 rounded-[8px] px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select a loan product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }} (Default: {{ $product->default_multiplier }}x, Max: {{ $product->max_multiplier }}x)
                                    </option>
                                @endforeach
                            </select>
                            @error('loanProductId') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Custom Multiplier</label>
                                <input type="number" step="0.1" min="0.1" max="10"
                                    wire:model="customMultiplier"
                                    class="w-full border border-slate-300 rounded-[8px] px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="e.g. 4.5">
                                <p class="mt-1 text-xs text-slate-500">Leave empty to use product default ({{ $products->firstWhere('id', $loanProductId)?->default_multiplier ?? 'N/A' }}x)</p>
                                @error('customMultiplier') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Custom Max Deduction %</label>
                                <input type="number" step="0.01" min="0" max="66.67"
                                    wire:model="customMaxDeductionPercent"
                                    class="w-full border border-slate-300 rounded-[8px] px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="e.g. 60">
                                <p class="mt-1 text-xs text-slate-500">Leave empty for default 33.33%. Max 66.67%. >50% requires second approval.</p>
                                @error('customMaxDeductionPercent') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Custom Max Amount (Optional)</label>
                            <input type="number" step="0.01" min="1"
                                wire:model="customMaxAmount"
                                class="w-full border border-slate-300 rounded-[8px] px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="e.g. 5000000">
                            <p class="mt-1 text-xs text-slate-500">Override absolute max amount for this member/product combination.</p>
                            @error('customMaxAmount') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Reason Category <span class="text-rose-500">*</span></label>
                            <select wire:model="reasonCategory" class="w-full border border-slate-300 rounded-[8px] px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select reason category</option>
                                @foreach ($reasonCategories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('reasonCategory') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Reason Details <span class="text-rose-500">*</span></label>
                            <textarea wire:model="reasonDetails" rows="3"
                                class="w-full border border-slate-300 rounded-[8px] px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Detailed justification (min 20 characters). Example: Member retiring Dec 2026, outstanding ₦800k, needs to recover before retirement. Approved by EXCO meeting 2026-07-15"></textarea>
                            <p class="mt-1 text-xs text-slate-500">Minimum 20 characters required.</p>
                            @error('reasonDetails') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Second Approval (Required if deduction > 50%)</label>
                            <select wire:model="secondApprovedBy" class="w-full border border-slate-300 rounded-[8px] px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select second approver</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->getRoleNames()->first() ?? 'No role' }})</option>
                                @endforeach
                            </select>
                            @error('secondApprovedBy') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Valid From <span class="text-rose-500">*</span></label>
                                <input type="date" wire:model="validFrom"
                                    class="w-full border border-slate-300 rounded-[8px] px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('validFrom') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Valid Until (Optional)</label>
                                <input type="date" wire:model="validUntil"
                                    class="w-full border border-slate-300 rounded-[8px] px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="mt-1 text-xs text-slate-500">Leave empty for ongoing override.</p>
                                @error('validUntil') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" wire:model="isActive" id="isActive" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <label for="isActive" class="ml-2 text-sm text-slate-700">Active</label>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                            <button type="button" wire:click="closeModal" class="btn-secondary">Cancel</button>
                            <button type="submit" class="btn-primary">{{ $editingOverride ? 'Update' : 'Create' }} Override</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>