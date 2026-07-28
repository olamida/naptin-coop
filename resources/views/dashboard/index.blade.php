<x-app-layout title="Dashboard">
    <div class="space-y-6 fade-in">
        {{-- Pending Alerts --}}
        @if ($pendingWithdrawals > 0 || $pendingGuarantors > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @if ($pendingWithdrawals > 0)
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl shadow-sm p-5 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <span class="material-symbols-outlined text-2xl text-white">account_balance_wallet</span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">Pending Withdrawals</h3>
                                    <p class="text-white/80 text-sm">{{ $pendingWithdrawals }} request(s) — ₦{{ number_format($pendingWithdrawalAmount) }} total</p>
                                </div>
                            </div>
                            <a href="{{ route('savings.pending-withdrawals') }}" class="bg-white/20 hover:bg-white/30 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">Review &rarr;</a>
                        </div>
                    </div>
                @endif

                @if ($pendingGuarantors > 0)
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-sm p-5 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <span class="material-symbols-outlined text-2xl text-white">group_add</span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">Guarantor Requests</h3>
                                    <p class="text-white/80 text-sm">{{ $pendingGuarantors }} pending guarantor response(s)</p>
                                </div>
                            </div>
                            <a href="{{ route('loans.index') }}?status=pending" class="bg-white/20 hover:bg-white/30 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">Review &rarr;</a>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Primary Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-600">group</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 font-medium">Total Members</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalMembers) }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-green-600 font-medium">{{ $activeMembers }} active</span>
                    <a href="{{ route('members.index') }}" class="text-xs text-blue-600 hover:underline font-medium">View &rarr;</a>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-600">savings</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 font-medium">Savings Balance</p>
                        <p class="text-2xl font-bold text-gray-800">₦{{ number_format($totalSavings) }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-green-600 font-medium">+₦{{ number_format($monthlyDeposits) }} this month</span>
                    <a href="{{ route('savings.index') }}" class="text-xs text-blue-600 hover:underline font-medium">View &rarr;</a>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-purple-600">trending_up</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 font-medium">Total Shares</p>
                        <p class="text-2xl font-bold text-gray-800">₦{{ number_format($totalShares) }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ $totalSavings + $totalShares > 0 ? round(($totalShares / ($totalSavings + $totalShares)) * 100) : 0 }}%"></div>
                    </div>
                    <a href="{{ route('shares.index') }}" class="text-xs text-blue-600 hover:underline font-medium ml-3 whitespace-nowrap">View &rarr;</a>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-orange-50 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-orange-600">account_balance</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 font-medium">Loans Outstanding</p>
                        <p class="text-2xl font-bold text-gray-800">₦{{ number_format($totalLoansOutstanding) }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-yellow-600 font-medium">{{ $pendingLoans }} pending</span>
                    <a href="{{ route('loans.index') }}" class="text-xs text-blue-600 hover:underline font-medium">View &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Arrears Alert --}}
        @if ($arrearsCount > 0)
            <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl shadow-sm p-5 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl text-white">warning</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Loan Arrears Alert</h3>
                            <p class="text-white/80 text-sm">{{ $arrearsCount }} loan(s) past maturity — ₦{{ number_format($arrearsAmount) }} outstanding</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-6 text-sm">
                        @if ($overdueCount > 0)
                            <div class="text-center">
                                <p class="text-2xl font-bold">{{ $overdueCount }}</p>
                                <p class="text-white/70 text-xs">Overdue</p>
                            </div>
                        @endif
                        @if ($defaultedLoans > 0)
                            <div class="text-center">
                                <p class="text-2xl font-bold">{{ $defaultedLoans }}</p>
                                <p class="text-white/70 text-xs">Defaulted</p>
                            </div>
                        @endif
                        <a href="{{ route('loans.index') }}?status=defaulted" class="bg-white/20 hover:bg-white/30 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">Review &rarr;</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Secondary Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-yellow-500 text-lg">pending_actions</span>
                    <p class="text-xs text-gray-500 font-medium">Pending Approvals</p>
                </div>
                <p class="text-xl font-bold text-gray-800">{{ number_format($pendingLoans) }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-xs text-gray-400">₦{{ number_format($pendingLoanAmount) }} value</p>
                    <a href="{{ route('loans.index') }}?status=pending" class="text-xs text-blue-600 hover:underline">View &rarr;</a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-blue-500 text-lg">autorenew</span>
                    <p class="text-xs text-gray-500 font-medium">Loans Repaid</p>
                </div>
                <p class="text-xl font-bold text-green-600">₦{{ number_format($totalLoansRepaid) }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-xs text-gray-400">of ₦{{ number_format($totalLoansDisbursed) }}</p>
                    <a href="{{ route('loans.index') }}?status=repaying" class="text-xs text-blue-600 hover:underline">View &rarr;</a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-indigo-500 text-lg">shopping_cart</span>
                    <p class="text-xs text-gray-500 font-medium">Purchases</p>
                </div>
                <p class="text-xl font-bold text-gray-800">₦{{ number_format($totalPurchases) }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-xs text-gray-400">{{ $activePurchases }} active</p>
                    <a href="{{ route('products.orders') }}" class="text-xs text-blue-600 hover:underline">View &rarr;</a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-emerald-500 text-lg">show_chart</span>
                    <p class="text-xs text-gray-500 font-medium">This Month</p>
                </div>
                <p class="text-xl font-bold text-green-600">+₦{{ number_format($monthlyDeposits) }}</p>
                <p class="text-xs text-gray-400 mt-2">-₦{{ number_format($monthlyWithdrawals) }} withdrawn</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-gray-500 text-lg">monitoring</span>
                    <p class="text-xs text-gray-500 font-medium">Net Position</p>
                </div>
                @php
                    $netPosition = ($totalSavings + $totalShares + $totalLoansRepaid) - $totalLoansOutstanding;
                @endphp
                <p class="text-xl font-bold {{ $netPosition >= 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($netPosition) }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-xs text-gray-400">Savings + Shares + Repaid</p>
                    <a href="{{ route('reports.index') }}" class="text-xs text-blue-600 hover:underline">Reports &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Financial Overview</h3>
                </div>
                <div style="position: relative; height: 300px;">
                    <canvas id="overviewChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Portfolio Mix</h3>
                <div style="position: relative; height: 250px;">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Monthly Trends Chart --}}
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800">6-Month Trends</h3>
                <div class="flex items-center gap-4 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Deposits</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span> Withdrawals</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span> Loans Disbursed</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Repayments</span>
                </div>
            </div>
            <div style="position: relative; height: 280px;">
                <canvas id="trendsChart"></canvas>
            </div>
        </div>

        {{-- Quick Actions + Activity Feed --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800">Quick Actions</h3>
                </div>
                <div class="p-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('members.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-blue-50 transition group">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition">
                            <span class="material-symbols-outlined text-blue-600">person_add</span>
                        </div>
                        <span class="text-xs font-medium text-gray-700 text-center">Add Member</span>
                    </a>
                    <a href="{{ route('savings.deposit') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-green-50 transition group">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition">
                            <span class="material-symbols-outlined text-green-600">savings</span>
                        </div>
                        <span class="text-xs font-medium text-gray-700 text-center">Record Deposit</span>
                    </a>
                    <a href="{{ route('loans.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-orange-50 transition group">
                        <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center group-hover:bg-orange-200 transition">
                            <span class="material-symbols-outlined text-orange-600">request_quote</span>
                        </div>
                        <span class="text-xs font-medium text-gray-700 text-center">New Loan</span>
                    </a>
                    <a href="{{ route('shares.purchase') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-purple-50 transition group">
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-200 transition">
                            <span class="material-symbols-outlined text-purple-600">trending_up</span>
                        </div>
                        <span class="text-xs font-medium text-gray-700 text-center">Buy Shares</span>
                    </a>
                    <a href="{{ route('payroll.compile') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-indigo-50 transition group">
                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center group-hover:bg-indigo-200 transition">
                            <span class="material-symbols-outlined text-indigo-600">receipt_long</span>
                        </div>
                        <span class="text-xs font-medium text-gray-700 text-center">Compile Payroll</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-gray-100 transition group">
                        <div class="w-10 h-10 bg-gray-200 rounded-xl flex items-center justify-center group-hover:bg-gray-300 transition">
                            <span class="material-symbols-outlined text-gray-600">assessment</span>
                        </div>
                        <span class="text-xs font-medium text-gray-700 text-center">Reports</span>
                    </a>
                </div>
            </div>

            {{-- Recent Activity Feed --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800">Recent Activity</h3>
                </div>
                <div class="divide-y divide-gray-50 max-h-[360px] overflow-y-auto">
                    @forelse ($recentActivity as $activity)
                        <div class="px-6 py-3">
                            <div class="flex items-start gap-3">
                                @php
                                    $eventIcons = [
                                        'member.registered' => ['person_add', 'bg-blue-100 text-blue-600'],
                                        'savings.deposit' => ['savings', 'bg-green-100 text-green-600'],
                                        'savings.withdrawal' => ['account_balance_wallet', 'bg-red-100 text-red-600'],
                                        'savings.withdrawal.approved' => ['check_circle', 'bg-green-100 text-green-600'],
                                        'savings.withdrawal.rejected' => ['cancel', 'bg-red-100 text-red-600'],
                                        'loan.applied' => ['request_quote', 'bg-yellow-100 text-yellow-600'],
                                        'loan.approved' => ['thumb_up', 'bg-green-100 text-green-600'],
                                        'loan.rejected' => ['thumb_down', 'bg-red-100 text-red-600'],
                                        'loan.disbursed' => ['account_balance', 'bg-blue-100 text-blue-600'],
                                        'loan.completed' => ['task_alt', 'bg-green-100 text-green-600'],
                                        'loan.repayment' => ['payments', 'bg-blue-100 text-blue-600'],
                                        'share.purchased' => ['trending_up', 'bg-purple-100 text-purple-600'],
                                        'dividend.declared' => ['parcels', 'bg-indigo-100 text-indigo-600'],
                                    ];
                                    $iconData = $eventIcons[$activity->event] ?? ['info', 'bg-gray-100 text-gray-600'];
                                @endphp
                                <div class="w-8 h-8 {{ $iconData[1] }} rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-sm">{{ $iconData[0] }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-gray-700 leading-snug">{{ $activity->description ?? $activity->event }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $activity->user->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-500">No recent activity.</div>
                    @endforelse
                </div>
            </div>

            {{-- Top Savers --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">Top Savers</h3>
                    <a href="{{ route('savings.accounts') }}" class="text-xs text-blue-600 hover:underline">View All &rarr;</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse ($topSavers as $index => $account)
                        <a href="{{ route('members.show', $account->member) }}" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition block">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-gray-400 w-5 text-center">#{{ $index + 1 }}</span>
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-xs font-bold text-green-700">
                                    {{ strtoupper(substr($account->member->first_name, 0, 1) . substr($account->member->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $account->member->first_name }} {{ $account->member->last_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $account->member->staff_id }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-green-600">₦{{ number_format($account->balance) }}</span>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-500">No savings accounts with balances yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Members --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">Recent Members</h3>
                    <a href="{{ route('members.index') }}" class="text-xs text-blue-600 hover:underline">View All &rarr;</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse ($recentMembers as $member)
                        <a href="{{ route('members.show', $member) }}" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition block">
                            <div class="flex items-center gap-3">
                                @if ($member->user && $member->user->avatar_url)
                                    <img src="{{ $member->user->avatar_url }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-xs font-bold text-gray-600">
                                        {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $member->first_name }} {{ $member->last_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $member->staff_id }} &middot; {{ $member->region->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $member->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-500">No members yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Loans --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">Recent Loans</h3>
                    <a href="{{ route('loans.index') }}" class="text-xs text-blue-600 hover:underline">View All &rarr;</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse ($recentLoans as $loan)
                        <a href="{{ route('loans.show', $loan) }}" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition block">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $loan->member->first_name ?? '' }} {{ $loan->member->last_name ?? '' }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $loan->loan_number }} &middot; ₦{{ number_format($loan->amount) }}</p>
                            </div>
                            @php
                                $loanColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'approved' => 'bg-purple-100 text-purple-700',
                                    'disbursed' => 'bg-orange-100 text-orange-700',
                                    'repaying' => 'bg-blue-100 text-blue-700',
                                    'completed' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'defaulted' => 'bg-gray-200 text-gray-700',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $loanColors[$loan->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-500">No loans yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Loan Status + Region Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Loan Portfolio</h3>
                <div style="position: relative; height: 200px;">
                    <canvas id="loanStatusChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Members by Region</h3>
                    <a href="{{ route('reports.index') }}" class="text-xs text-blue-600 hover:underline">Reports &rarr;</a>
                </div>
                <div class="space-y-3">
                    @forelse ($regionStats as $stat)
                        <div class="flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-700 font-medium">{{ $stat->region->name ?? 'Unknown' }}</span>
                                    <span class="text-xs text-gray-500">{{ $stat->total }} members</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $totalMembers > 0 ? round(($stat->total / $totalMembers) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No region data.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Overview bar chart
            const overviewCtx = document.getElementById('overviewChart');
            if (overviewCtx) {
                new Chart(overviewCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Savings', 'Shares', 'Loans Disbursed', 'Loans Repaid', 'Loans Outstanding', 'Purchases'],
                        datasets: [{
                            label: 'Amount (₦)',
                            data: [
                                {{ $totalSavings }},
                                {{ $totalShares }},
                                {{ $totalLoansDisbursed }},
                                {{ $totalLoansRepaid }},
                                {{ $totalLoansOutstanding }},
                                {{ $totalPurchases }}
                            ],
                            backgroundColor: [
                                '#22c55e',
                                '#a855f7',
                                '#f97316',
                                '#3b82f6',
                                '#ef4444',
                                '#6366f1',
                            ],
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    callback: function(value) {
                                        return '₦' + (value/1000000).toFixed(1) + 'M';
                                    }
                                }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Portfolio Mix doughnut
            const distCtx = document.getElementById('distributionChart');
            if (distCtx) {
                new Chart(distCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Savings', 'Shares', 'Loans Outstanding'],
                        datasets: [{
                            data: [{{ $totalSavings }}, {{ $totalShares }}, {{ $totalLoansOutstanding }}],
                            backgroundColor: ['#22c55e', '#a855f7', '#f97316'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, font: { size: 11 } } } }
                    }
                });
            }

            // Loan Status horizontal bar
            const loanCtx = document.getElementById('loanStatusChart');
            if (loanCtx) {
                const statusLabels = {!! json_encode(array_keys($loanStatusCounts->toArray())) !!};
                const statusData = {!! json_encode(array_values($loanStatusCounts->toArray())) !!};
                const statusColors = {
                    'pending': '#eab308',
                    'approved': '#a855f7',
                    'disbursed': '#f97316',
                    'repaying': '#3b82f6',
                    'completed': '#22c55e',
                    'rejected': '#ef4444',
                    'defaulted': '#6b7280',
                };
                new Chart(loanCtx, {
                    type: 'bar',
                    data: {
                        labels: statusLabels.map(function(l) { return l.charAt(0).toUpperCase() + l.slice(1); }),
                        datasets: [{
                            data: statusData,
                            backgroundColor: statusLabels.map(function(l) { return statusColors[l] || '#9ca3af'; }),
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1 } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            }

            // Monthly Trends line chart
            const trendsCtx = document.getElementById('trendsChart');
            if (trendsCtx) {
                const trendLabels = {!! json_encode(array_column($monthlyTrends, 'label')) !!};
                new Chart(trendsCtx, {
                    type: 'line',
                    data: {
                        labels: trendLabels,
                        datasets: [
                            {
                                label: 'Deposits',
                                data: {!! json_encode(array_column($monthlyTrends, 'deposits')) !!},
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#22c55e',
                            },
                            {
                                label: 'Withdrawals',
                                data: {!! json_encode(array_column($monthlyTrends, 'withdrawals')) !!},
                                borderColor: '#f87171',
                                backgroundColor: 'rgba(248, 113, 113, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#f87171',
                            },
                            {
                                label: 'Loans Disbursed',
                                data: {!! json_encode(array_column($monthlyTrends, 'loan_disbursed')) !!},
                                borderColor: '#f97316',
                                backgroundColor: 'transparent',
                                tension: 0.4,
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointRadius: 3,
                                pointBackgroundColor: '#f97316',
                            },
                            {
                                label: 'Repayments',
                                data: {!! json_encode(array_column($monthlyTrends, 'loan_repayments')) !!},
                                borderColor: '#3b82f6',
                                backgroundColor: 'transparent',
                                tension: 0.4,
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointRadius: 3,
                                pointBackgroundColor: '#3b82f6',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return ctx.dataset.label + ': ₦' + ctx.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    callback: function(value) {
                                        return '₦' + (value/1000000).toFixed(1) + 'M';
                                    }
                                }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
