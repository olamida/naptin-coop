<x-portal-layout title="Guarantor Requests">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'My Account', 'url' => route('portal.dashboard')], ['label' => 'Guarantor Requests']]" />
        <div class="flex items-center gap-3">
            <a href="{{ route('portal.dashboard') }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
            <h2 class="text-2xl font-bold text-gray-800">My Guarantor Requests</h2>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-amber-600 mt-0.5">info</span>
                <div class="text-sm text-amber-800">
                    <p class="font-medium">Guarantor Requests</p>
                    <p class="text-xs text-amber-700 mt-1">These are loan applications where you have been requested to serve as a guarantor. Please review and respond promptly. As a guarantor, you are confirming that you will be responsible for the loan if the borrower defaults.</p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($guarantorRequests as $request)
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                        'accepted' => 'bg-green-100 text-green-700 border-green-200',
                        'declined' => 'bg-red-100 text-red-700 border-red-200',
                    ];
                    $statusIcons = [
                        'pending' => 'pending',
                        'accepted' => 'check_circle',
                        'declined' => 'cancel',
                    ];
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-blue-600 text-xl">person</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">
                                        {{ $request->loan->member->first_name ?? 'Unknown' }} {{ $request->loan->member->last_name ?? '' }}
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        {{ $request->loan->member->staff_id ?? 'N/A' }}
                                        &middot; Applied {{ $request->loan->application_date?->format('d M Y') ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full border {{ $statusColors[$request->status->value] }}">
                                {{ $request->status->label() }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-[10px] text-gray-500 uppercase">Loan Number</p>
                                <p class="text-sm font-mono font-medium">{{ $request->loan->loan_number }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-[10px] text-gray-500 uppercase">Amount</p>
                                <p class="text-sm font-bold">₦{{ number_format($request->loan->amount, 2) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-[10px] text-gray-500 uppercase">Type</p>
                                <p class="text-sm font-medium">{{ ucfirst($request->loan->type) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-[10px] text-gray-500 uppercase">Monthly Repayment</p>
                                <p class="text-sm font-medium">₦{{ number_format($request->loan->monthly_repayment, 2) }}</p>
                            </div>
                        </div>

                        @if ($request->loan->purpose)
                            <div class="mt-3 text-xs text-gray-500">
                                <span class="font-medium">Purpose:</span> {{ $request->loan->purpose }}
                            </div>
                        @endif

                        @if ($request->notes)
                            <div class="mt-2 text-xs text-gray-500 italic">
                                Your note: "{{ $request->notes }}"
                            </div>
                        @endif
                    </div>

                    @if ($request->status->value === 'pending')
                        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center gap-3">
                            <form method="POST" action="{{ route('portal.guarantor.update', $request) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="status" value="accepted">
                                <button type="submit" class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                    <span class="material-symbols-outlined text-[16px]">check</span>
                                    Accept
                                </button>
                            </form>
                            <form method="POST" action="{{ route('portal.guarantor.update', $request) }}" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="status" value="declined">
                                <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                                    onclick="return confirm('Are you sure you want to decline this guarantor request?')">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                    Decline
                                </button>
                            </form>
                            <span class="text-xs text-gray-400">Responded: {{ $request->responded_at?->format('d M Y H:i') ?? 'Not yet' }}</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">group_add</span>
                    <h3 class="text-lg font-medium text-gray-700">No Guarantor Requests</h3>
                    <p class="text-sm text-gray-500 mt-1">You have not been asked to guarantee any loans.</p>
                </div>
            @endforelse
        </div>

        {{ $guarantorRequests->links() }}
    </div>
</x-portal-layout>
