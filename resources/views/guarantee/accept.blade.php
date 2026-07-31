<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Guarantor Request</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto">
            @if (session('success'))
                <div class="mb-6 rounded-[10px] bg-emerald-50 dark:bg-emerald-900/20 p-4 text-emerald-700 dark:text-emerald-300 text-sm">
                    <span class="material-symbols-outlined align-middle text-lg mr-1">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-[10px] bg-red-50 dark:bg-red-900/20 p-4 text-red-700 dark:text-red-300 text-sm">
                    <span class="material-symbols-outlined align-middle text-lg mr-1">error</span>
                    {{ session('error') }}
                </div>
            @endif

            @if ($alreadyResponded)
                <div class="bg-white dark:bg-gray-800 rounded-[16px] shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
                    <span class="material-symbols-outlined text-5xl text-gray-400 mb-4">how_to_reg</span>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ $message }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No further action is needed.</p>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-[16px] shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="bg-[#0F172A] px-6 py-5">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-white text-3xl">handshake</span>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Guarantor Request</h3>
                                <p class="text-sm text-gray-300">You have been invited to be a guarantor for a loan</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Applicant</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $guarantor->loan->member->first_name }} {{ $guarantor->loan->member->last_name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Staff ID</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $guarantor->loan->member->staff_id }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Loan Amount</p>
                                <p class="text-sm font-mono font-medium text-gray-900 dark:text-white">₦{{ number_format($guarantor->loan->amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Loan Product</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $guarantor->loan->loanProduct?->name ?? $guarantor->loan->type }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $guarantor->loan->tenure_months }} months</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monthly Repayment</p>
                                <p class="text-sm font-mono font-medium text-gray-900 dark:text-white">₦{{ number_format($guarantor->loan->monthly_repayment, 2) }}</p>
                            </div>
                        </div>

                        <div class="rounded-[10px] bg-amber-50 dark:bg-amber-900/20 p-4 text-amber-700 dark:text-amber-300 text-sm flex items-start gap-2">
                            <span class="material-symbols-outlined text-lg flex-shrink-0">info</span>
                            <p>By accepting, you confirm your willingness to act as guarantor. This means you accept responsibility if the applicant defaults on this loan.</p>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <form method="POST" action="{{ route('guarantee.respond', $guarantor->accept_token) }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="action" value="accept">
                                <button type="submit"
                                    class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[10px] font-medium transition-colors flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-lg">check_circle</span>
                                    Accept as Guarantor
                                </button>
                            </form>
                            <form method="POST" action="{{ route('guarantee.respond', $guarantor->accept_token) }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="action" value="decline">
                                <button type="submit"
                                    class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-[10px] font-medium transition-colors flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-lg">cancel</span>
                                    Decline
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
