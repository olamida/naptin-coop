<x-app-layout title="{{ $member->first_name }} {{ $member->last_name }}">
        <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Members', 'url' => route('members.index')],
            ['label' => $member->full_name],
        ]" />
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('members.index') }}" class="text-gray-500 hover:text-gray-700">&larr;</a>
                <h2 class="text-2xl font-bold text-gray-800">{{ $member->first_name }} {{ $member->last_name }}</h2>
                <span class="px-2 py-1 text-xs rounded-full
                    {{ $member->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $member->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $member->status === 'inactive' ? 'bg-gray-100 text-gray-600' : '' }}
                    {{ $member->status === 'retired' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $member->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ $member->status === 'pending' ? 'Pending Approval' : ucfirst($member->status) }}
                </span>
            </div>
            <div class="flex items-center gap-2" x-data="{ actionsOpen: false }">
                @if ($member->status === 'pending')
                    <form method="POST" action="{{ route('members.approve', $member) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('Approve this member? They will receive login credentials via email.')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                            Approve
                        </button>
                    </form>
                    <form method="POST" action="{{ route('members.reject', $member) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('Reject this member registration?')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">cancel</span>
                            Reject
                        </button>
                    </form>
                @endif

                <a href="{{ route('reports.member-status', $member) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">assessment</span>
                    Report
                </a>

                {{-- Actions Dropdown --}}
                <div class="relative" @click.away="actionsOpen = false">
                    <button @click="actionsOpen = !actionsOpen" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">arrow_drop_down</span>
                        Actions
                    </button>
                    <div x-show="actionsOpen" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                        @can('edit-members')
                            <a href="{{ route('members.edit', $member) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <span class="material-symbols-outlined text-lg">edit</span>
                                Edit Member
                            </a>
                        @endcan
                        <a href="{{ route('products.index', ['member_id' => $member->id]) }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <span class="material-symbols-outlined text-lg">shopping_cart</span>
                            Order for Member
                        </a>
                        <a href="{{ route('members.import') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <span class="material-symbols-outlined text-lg">upload_file</span>
                            Import Data
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Personal Details</h3>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Staff ID</dt>
                            <dd class="font-mono font-medium">{{ $member->staff_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Region</dt>
                            <dd class="font-medium">{{ $member->region->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd class="font-medium">{{ $member->email ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Phone</dt>
                            <dd class="font-medium">{{ $member->phone ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Gender</dt>
                            <dd class="font-medium">{{ ucfirst($member->gender ?? 'N/A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Date of Birth</dt>
                            <dd class="font-medium">{{ $member->date_of_birth?->format('d M Y') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">NIN</dt>
                            <dd class="font-medium">{{ $member->nin ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">State of Origin</dt>
                            <dd class="font-medium">{{ $member->state_of_origin ?? 'N/A' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Address</dt>
                            <dd class="font-medium">{{ $member->address ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Employment Details</h3>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Grade Level</dt>
                            <dd class="font-medium">{{ $member->grade_level ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Monthly Salary</dt>
                            <dd class="font-medium">₦{{ number_format($member->monthly_salary, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Employment Date</dt>
                            <dd class="font-medium">{{ $member->employment_date?->format('d M Y') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Retirement Date</dt>
                            <dd class="font-medium">{{ $member->retirement_date?->format('d M Y') ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($member->positions->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Positions</h3>
                        <div class="space-y-2">
                            @foreach ($member->positions as $position)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg text-sm">
                                    <span class="font-medium">{{ $position->name }}</span>
                                    <span class="text-xs text-gray-500">
                                        {{ $position->pivot->start_date?->format('d M Y') ?? 'N/A' }}
                                        @if ($position->pivot->is_current)
                                            <span class="text-green-600 font-medium">(Current)</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($member->loans->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Loans</h3>
                            <a href="{{ route('members.loans-detail', $member) }}" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                View All
                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </a>
                        </div>
                        <div class="space-y-2">
                            @foreach ($member->loans as $loan)
                                <a href="{{ route('loans.show', $loan) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg text-sm hover:bg-gray-100 transition">
                                    <div>
                                        <span class="font-mono text-xs text-gray-500">{{ $loan->loan_number }}</span>
                                        <span class="font-medium ml-2">₦{{ number_format($loan->amount, 2) }}</span>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $loan->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $loan->status === 'repaying' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $loan->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $loan->status === 'approved' ? 'bg-purple-100 text-purple-700' : '' }}
                                        {{ $loan->status === 'disbursed' ? 'bg-orange-100 text-orange-700' : '' }}
                                        {{ $loan->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Purchases</h3>
                        <a href="{{ route('members.purchases-detail', $member) }}" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                            View All
                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </a>
                    </div>
                    @php $purchases = $member->purchaseOrders()->with('product')->latest()->limit(5)->get(); @endphp
                    @if ($purchases->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($purchases as $order)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg text-sm">
                                    <div>
                                        <span class="font-mono text-xs text-gray-500">{{ $order->order_number }}</span>
                                        <span class="font-medium ml-2">{{ $order->product->name ?? 'N/A' }}</span>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $order->status === 'approved' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $order->status === 'active' ? 'bg-amber-100 text-amber-700' : '' }}
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No purchases yet.</p>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Monthly Savings Preference</h3>
                    <div x-data="{ editing: false, value: '{{ $member->monthly_savings ?? '' }}' }">
                        <div x-show="!editing">
                            <p class="text-2xl font-bold text-indigo-700">₦{{ number_format($member->monthly_savings ?? 0, 2) }}</p>
                            <p class="text-xs text-gray-400 mt-1">Set in member profile for payroll deductions</p>
                            <button x-on:click="editing = true" class="mt-2 text-xs text-blue-600 hover:underline">Change Amount</button>
                        </div>
                        <div x-show="editing" x-cloak>
                            <form method="POST" action="{{ route('members.update', $member) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="region_id" value="{{ $member->region_id }}">
                                <input type="hidden" name="staff_id" value="{{ $member->staff_id }}">
                                <input type="hidden" name="first_name" value="{{ $member->first_name }}">
                                <input type="hidden" name="last_name" value="{{ $member->last_name }}">
                                <input type="hidden" name="status" value="{{ $member->status }}">
                                <input type="number" step="0.01" name="monthly_savings" x-model="value"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none mb-2">
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium">Save</button>
                                    <button type="button" x-on:click="editing = false" class="text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Savings Account</h3>
                        @if ($member->savingsAccount)
                            <a href="{{ route('members.savings-detail', $member) }}" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                View All
                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </a>
                        @endif
                    </div>
                    @if ($member->savingsAccount)
                        <div class="space-y-3">
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-xs text-gray-500">Balance</p>
                                <p class="text-2xl font-bold text-green-700">₦{{ number_format($member->savingsAccount->balance, 2) }}</p>
                            </div>
                            <div class="text-sm">
                                <p class="text-gray-500">Account: <span class="font-mono">{{ $member->savingsAccount->account_number }}</span></p>
                                <p class="text-gray-500">Status: {{ ucfirst($member->savingsAccount->status) }}</p>
                            </div>
                            @if ($member->savingsAccount->transactions->isNotEmpty())
                                <div class="border-t border-gray-100 pt-3">
                                    <p class="text-xs font-medium text-gray-500 mb-2">Recent Transactions</p>
                                    @foreach ($member->savingsAccount->transactions->take(5) as $txn)
                                        <div class="flex justify-between text-xs py-1">
                                            <span class="text-gray-600">{{ ucfirst($txn->type) }}</span>
                                            <span class="{{ $txn->type === 'deposit' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $txn->type === 'deposit' ? '+' : '-' }}₦{{ number_format($txn->amount, 2) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No savings account found.</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Share Account</h3>
                        <a href="{{ route('shares.index') }}" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                            View All
                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </a>
                    </div>
                    @if ($member->shareAccount)
                        <div class="space-y-3">
                            <div class="text-center p-4 bg-purple-50 rounded-lg">
                                <p class="text-xs text-gray-500">Total Shares</p>
                                <p class="text-2xl font-bold text-purple-700">{{ number_format($member->shareAccount->total_shares) }}</p>
                            </div>
                            <div class="text-sm space-y-1">
                                <p class="text-gray-500">Total Value: <span class="font-medium">₦{{ number_format($member->shareAccount->total_value, 2) }}</span></p>
                                <p class="text-gray-500">Share Price: <span class="font-medium">₦{{ number_format($member->shareAccount->share_price, 2) }}</span></p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No share account found.</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">Next of Kin</h3>
                    @if ($member->nextOfKins->isNotEmpty())
                        <div class="space-y-3 mb-4">
                            @foreach ($member->nextOfKins as $kin)
                                <div class="flex items-start justify-between p-3 bg-gray-50 rounded-lg text-sm">
                                    <div>
                                        <p class="font-medium">{{ $kin->name }} @if ($kin->is_primary) <span class="text-xs text-blue-600">(Primary)</span> @endif</p>
                                        <p class="text-gray-500 text-xs">{{ $kin->relationship }} &middot; {{ $kin->phone ?? 'N/A' }}</p>
                                    </div>
                                    <form id="delete-kin-{{ $kin->id }}" method="POST" action="{{ route('members.next-of-kin.destroy', [$member, $kin]) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="deleteConfirm('delete-kin-{{ $kin->id }}')" class="text-gray-400 hover:text-red-600">
                                            <span class="material-symbols-outlined text-lg">close</span>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <form method="POST" action="{{ route('members.next-of-kin.store', $member) }}" class="space-y-3 border-t border-gray-100 pt-4">
                        @csrf
                        <p class="text-xs font-medium text-gray-500">Add New</p>
                        <input type="text" name="name" placeholder="Full name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" name="relationship" placeholder="Relationship" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="text" name="phone" placeholder="Phone"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <input type="email" name="email" placeholder="Email (optional)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <label class="flex items-center gap-2 text-xs">
                            <input type="checkbox" name="is_primary" value="1" class="w-3 h-3 text-blue-600 rounded">
                            Set as primary next of kin
                        </label>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition">
                            Add Next of Kin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
