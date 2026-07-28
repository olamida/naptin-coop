<x-app-layout title="Loans">
    <div class="space-y-6">
        <x-breadcrumb :items="[['label' => 'Loans']]" />
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Loans</h2>
            <div class="flex items-center gap-2">
                @can('view-loans')
                    <a href="{{ route('loans.export') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        Export
                    </a>
                @endcan
                @can('repay-loans')
                    <a href="{{ route('loans.import') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">upload_file</span>
                        Import Repayments
                    </a>
                @endcan
                @can('create-loans')
                    <a href="{{ route('loans.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-lg">add</span>
                        New Loan
                    </a>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Total Loans</p>
                <p class="text-xl font-bold">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Outstanding</p>
                <p class="text-xl font-bold text-orange-600">₦{{ number_format($stats['outstanding'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Repaying</p>
                <p class="text-xl font-bold text-blue-600">{{ $stats['repaying'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <p class="text-xs text-gray-500">Pending</p>
                <p class="text-xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 border {{ $stats['defaulted'] > 0 ? 'border-red-200 bg-red-50' : 'border-gray-100' }}">
                <p class="text-xs text-gray-500">Defaulted</p>
                <p class="text-xl font-bold {{ $stats['defaulted'] > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $stats['defaulted'] }}</p>
                @if ($stats['defaulted'] > 0)
                    <p class="text-[10px] text-red-500 mt-0.5">₦{{ number_format($stats['defaulted_amount'], 2) }} at risk</p>
                @endif
            </div>
        </div>

        {{-- Sub-Navigation Tabs --}}
        <div class="flex items-center gap-1 border-b border-gray-200 overflow-x-auto">
            <a href="{{ route('loans.index') }}" class="px-4 py-2.5 text-sm font-medium border-b-2 {{ !request('status') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}} -mb-px transition whitespace-nowrap">
                All
            </a>
            <a href="{{ route('loans.index', ['status' => 'pending']) }}" class="px-4 py-2.5 text-sm font-medium border-b-2 {{ request('status') === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}} -mb-px transition whitespace-nowrap">
                Pending {{ $stats['pending'] > 0 ? "({$stats['pending']})" : '' }}
            </a>
            <a href="{{ route('loans.index', ['status' => 'approved']) }}" class="px-4 py-2.5 text-sm font-medium border-b-2 {{ request('status') === 'approved' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}} -mb-px transition whitespace-nowrap">
                Approved
            </a>
            <a href="{{ route('loans.index', ['status' => 'repaying']) }}" class="px-4 py-2.5 text-sm font-medium border-b-2 {{ request('status') === 'repaying' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}} -mb-px transition whitespace-nowrap">
                Repaying {{ $stats['repaying'] > 0 ? "({$stats['repaying']})" : '' }}
            </a>
            <a href="{{ route('loans.index', ['status' => 'completed']) }}" class="px-4 py-2.5 text-sm font-medium border-b-2 {{ request('status') === 'completed' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}} -mb-px transition whitespace-nowrap">
                Completed
            </a>
            @if ($stats['defaulted'] > 0)
                <a href="{{ route('loans.index', ['status' => 'defaulted']) }}" class="px-4 py-2.5 text-sm font-medium border-b-2 {{ request('status') === 'defaulted' ? 'border-red-600 text-red-600' : 'border-transparent text-red-500 hover:text-red-700 hover:border-red-300'}} -mb-px transition whitespace-nowrap">
                    ⚠ Defaulted ({{ $stats['defaulted'] }})
                </a>
            @endif
        </div>

        @can('manage-loan-products')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-amber-500 text-xl">category</span>
                    <div>
                        <p class="font-semibold text-gray-800">Loan Products</p>
                        <p class="text-xs text-gray-500">Configure loan types, interest rates, and limits</p>
                    </div>
                </div>
                <a href="{{ route('admin.loan-products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    Manage Products
                </a>
            </div>
        @endcan

        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Member name, Staff ID, or Loan #..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Status</option>
                    @foreach (\App\Enums\LoanStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Filter</button>
            @if (request()->hasAny(['search', 'status']))
                <a href="{{ route('loans.index') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Loan #</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Member</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Outstanding</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($loans as $loan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs">{{ $loan->loan_number }}</td>
                            <td class="px-4 py-3 font-medium">{{ $loan->member->first_name ?? '' }} {{ $loan->member->last_name ?? '' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ ucfirst($loan->type) }}</td>
                            <td class="px-4 py-3 text-right">₦{{ number_format($loan->amount, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium">₦{{ number_format($loan->outstanding, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $loan->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $loan->status === 'repaying' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $loan->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $loan->status === 'approved' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $loan->status === 'disbursed' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $loan->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $loan->status === 'defaulted' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('loans.show', $loan) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No loans found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $loans->links() }}

        <x-show-all-toggle />
    </div>
</x-app-layout>
