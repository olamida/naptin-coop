<x-app-layout title="Dashboard">
    <div class="space-y-6 fade-in">
        {{-- Global Command Palette Trigger --}}
        <div class="max-w-3xl">
            <button @click="window.dispatchEvent(new CustomEvent('open-command-palette'))"
                    class="w-full flex items-center justify-between bg-white border border-slate-200 rounded-[12px] px-4 py-3 text-sm text-slate-500 hover:border-slate-300 hover:text-slate-700 transition shadow-sm">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-slate-400">search</span>
                    Search members, loans, references... (REG/2026/001, SAV/DEP/...)
                </span>
                <kbd class="bg-slate-100 px-2 py-1 rounded text-xs font-mono">⌘K</kbd>
            </button>
        </div>

        {{-- Pending Alerts --}}
        @if ($pendingWithdrawals > 0 || $pendingGuarantors > 0 || $arrearsCount > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
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
                                    <p class="text-white/80 text-sm">{{ $pendingGuarantors }} pending response(s)</p>
                                </div>
                            </div>
                            <a href="{{ route('loans.index') }}?status=pending" class="bg-white/20 hover:bg-white/30 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">Review &rarr;</a>
                        </div>
                    </div>
                @endif

                @if ($arrearsCount > 0)
                    <div class="bg-gradient-to-r from-rose-600 to-red-600 rounded-xl shadow-sm p-5 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <span class="material-symbols-outlined text-2xl text-white">warning</span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">Loan Arrears</h3>
                                    <p class="text-white/80 text-sm">{{ $arrearsCount }} loans — ₦{{ number_format($arrearsAmount) }} outstanding</p>
                                </div>
                            </div>
                            <a href="{{ route('loans.index') }}?status=defaulted" class="bg-white/20 hover:bg-white/30 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">Review &rarr;</a>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- KPI Cards (Command Center style) --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer" onclick="window.location='{{ route('savings.index') }}'">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Total Savings Today</p>
                <p class="mt-2 text-2xl font-mono font-bold text-[#0F172A] truncate" title="₦{{ number_format($totalSavings) }}">₦{{ number_format($totalSavings) }}</p>
                <div class="flex items-center justify-between mt-1">
                    <p class="text-xs {{ $savingsChangePercent >= 0 ? 'text-emerald-600' : 'text-rose-600' }} flex items-center gap-1">
                        <span>{!! $savingsChangePercent >= 0 ? '↑' : '↓' !!}</span>
                        {{ abs($savingsChangePercent) }}% vs yesterday
                    </p>
                    <span class="w-16 h-6" id="savingsSparkline"></span>
                </div>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer" onclick="window.location='{{ route('loans.index', ['status' => 'defaulted']) }}'">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Loans in Arrears</p>
                <p class="mt-2 text-2xl font-mono font-bold text-amber-600">{{ $arrearsCount }} <span class="text-sm font-normal text-slate-400">• ₦{{ number_format($arrearsAmount) }}</span></p>
                <p class="text-xs text-slate-500 mt-1">{{ $overdueCount }} overdue &middot; {{ $defaultedLoans }} defaulted</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer" onclick="window.location='{{ route('loans.index', ['status' => 'pending']) }}'">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Pending Approvals</p>
                <p class="mt-2 text-2xl font-mono font-bold text-[#0F172A]">{{ $pendingLoans + $pendingWithdrawals }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $pendingLoans }} loans &middot; {{ $pendingWithdrawals }} withdrawals</p>
            </div>
            <div class="bg-white rounded-[16px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer" onclick="window.location='{{ route('payroll.index') }}'">
                <p class="text-xs font-semibold text-slate-500 tracking-wider uppercase">Payroll Due</p>
                <p class="mt-2 text-2xl font-mono font-bold {{ $payrollDueDays !== null && $payrollDueDays <= 3 ? 'text-amber-600' : 'text-[#0F172A]' }} truncate" title="{{ $payrollDueText }}">{{ $payrollDueText }}</p>
                <p class="text-xs text-slate-500 mt-1">
                    @if ($nextPayroll)
                        {{ Carbon\Carbon::parse('1 ' . $nextPayroll->month . ' ' . $nextPayroll->year)->format('F Y') }} • ₦{{ number_format($nextPayroll->grand_total ?? 0) }}
                    @else
                        No pending payroll
                    @endif
                </p>
            </div>
        </div>

        {{-- Action Queue --}}
        <div>
            <h3 class="text-sm font-semibold text-[#0F172A] mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500 inline-block animate-pulse"></span>
                Needs Your Attention
            </h3>
            <div class="grid md:grid-cols-3 gap-3">
                <div class="bg-white border border-amber-200 rounded-[16px] p-4 flex justify-between items-center hover:shadow-sm transition">
                    <div>
                        <p class="text-sm font-medium">{{ $pendingWithdrawals > 0 ? $pendingWithdrawals : '0' }} Pending Withdrawal Requests</p>
                        <p class="text-xs text-slate-500">₦{{ number_format($pendingWithdrawalAmount) }} total</p>
                    </div>
                    <a href="{{ route('savings.pending-withdrawals') }}" class="bg-[#0F172A] text-white text-xs px-3 py-2 rounded-[10px] hover:bg-slate-800 transition">Review</a>
                </div>
                <div class="bg-white border border-slate-200 rounded-[16px] p-4 flex justify-between items-center hover:shadow-sm transition">
                    <div>
                        <p class="text-sm font-medium">{{ $pendingGuarantors }} Guarantor Invites Pending</p>
                        <p class="text-xs text-slate-500">Awaiting member responses</p>
                    </div>
                    <a href="{{ route('loans.index') }}?status=pending" class="bg-white border border-slate-300 text-xs px-3 py-2 rounded-[10px] hover:bg-slate-50 transition">View</a>
                </div>
                <div class="bg-white border border-slate-200 rounded-[16px] p-4 flex justify-between items-center hover:shadow-sm transition">
                    <div>
                        <p class="text-sm font-medium">{{ $pendingOrders }} Pending Purchase Orders</p>
                        <p class="text-xs text-slate-500">Awaiting approval</p>
                    </div>
                    <a href="{{ route('products.orders') }}" class="bg-white border border-slate-300 text-xs px-3 py-2 rounded-[10px] hover:bg-slate-50 transition">View</a>
                </div>
            </div>
        </div>

        {{-- Secondary Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-yellow-500 text-lg">pending_actions</span>
                    <p class="text-xs text-slate-500 font-medium">Pending Approvals</p>
                </div>
                <p class="text-xl font-bold text-[#0F172A]">{{ number_format($pendingLoans) }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-xs text-slate-400">₦{{ number_format($pendingLoanAmount) }} value</p>
                    <a href="{{ route('loans.index') }}?status=pending" class="text-xs text-blue-600 hover:underline">View &rarr;</a>
                </div>
            </div>

            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-blue-500 text-lg">autorenew</span>
                    <p class="text-xs text-slate-500 font-medium">Loans Repaid</p>
                </div>
                <p class="text-xl font-bold text-green-600">₦{{ number_format($totalLoansRepaid) }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-xs text-slate-400">of ₦{{ number_format($totalLoansDisbursed) }}</p>
                    <a href="{{ route('loans.index') }}?status=repaying" class="text-xs text-blue-600 hover:underline">View &rarr;</a>
                </div>
            </div>

            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-indigo-500 text-lg">shopping_cart</span>
                    <p class="text-xs text-slate-500 font-medium">Purchases</p>
                </div>
                <p class="text-xl font-bold text-[#0F172A]">₦{{ number_format($totalPurchases) }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-xs text-slate-400">{{ $activePurchases }} active</p>
                    <a href="{{ route('products.orders') }}" class="text-xs text-blue-600 hover:underline">View &rarr;</a>
                </div>
            </div>

            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-emerald-500 text-lg">show_chart</span>
                    <p class="text-xs text-slate-500 font-medium">This Month</p>
                </div>
                <p class="text-xl font-bold text-green-600">+₦{{ number_format($monthlyDeposits) }}</p>
                <p class="text-xs text-slate-400 mt-2">-₦{{ number_format($monthlyWithdrawals) }} withdrawn</p>
            </div>

            <div class="bg-white rounded-[16px] shadow-sm p-4 border border-slate-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-slate-500 text-lg">monitoring</span>
                    <p class="text-xs text-slate-500 font-medium">Net Position</p>
                </div>
                @php
                    $netPosition = ($totalSavings + $totalShares + $totalLoansRepaid) - $totalLoansOutstanding;
                @endphp
                <p class="text-xl font-bold {{ $netPosition >= 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($netPosition) }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-xs text-slate-400">Savings + Shares + Repaid</p>
                    <a href="{{ route('reports.index') }}" class="text-xs text-blue-600 hover:underline">Reports &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-[16px] shadow-sm p-6 border border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-[#0F172A]">Financial Overview</h3>
                </div>
                <div style="position: relative; height: 300px;">
                    <canvas id="overviewChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-[16px] shadow-sm p-6 border border-slate-200">
                <h3 class="text-sm font-semibold text-[#0F172A] mb-4">Portfolio Mix</h3>
                <div style="position: relative; height: 250px;">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Monthly Trends Chart --}}
        <div class="bg-white rounded-[16px] shadow-sm p-6 border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-[#0F172A]">6-Month Trends</h3>
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
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-[#0F172A]">Quick Actions</h3>
                </div>
                <div class="p-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('members.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-[16px] hover:bg-slate-100 transition group">
                        <div class="w-10 h-10 bg-blue-100 rounded-[16px] flex items-center justify-center group-hover:bg-blue-200 transition">
                            <span class="material-symbols-outlined text-blue-600">person_add</span>
                        </div>
                        <span class="text-xs font-medium text-slate-700 text-center">Add Member</span>
                    </a>
                    <a href="{{ route('savings.deposit') }}" class="flex flex-col items-center gap-2 p-4 rounded-[16px] hover:bg-green-50 transition group">
                        <div class="w-10 h-10 bg-green-100 rounded-[16px] flex items-center justify-center group-hover:bg-green-200 transition">
                            <span class="material-symbols-outlined text-green-600">savings</span>
                        </div>
                        <span class="text-xs font-medium text-slate-700 text-center">Record Deposit</span>
                    </a>
                    <a href="{{ route('loans.create') }}" class="flex flex-col items-center gap-2 p-4 rounded-[16px] hover:bg-orange-50 transition group">
                        <div class="w-10 h-10 bg-orange-100 rounded-[16px] flex items-center justify-center group-hover:bg-orange-200 transition">
                            <span class="material-symbols-outlined text-orange-600">request_quote</span>
                        </div>
                        <span class="text-xs font-medium text-slate-700 text-center">New Loan</span>
                    </a>
                    <a href="{{ route('shares.purchase') }}" class="flex flex-col items-center gap-2 p-4 rounded-[16px] hover:bg-purple-50 transition group">
                        <div class="w-10 h-10 bg-purple-100 rounded-[16px] flex items-center justify-center group-hover:bg-purple-200 transition">
                            <span class="material-symbols-outlined text-purple-600">trending_up</span>
                        </div>
                        <span class="text-xs font-medium text-slate-700 text-center">Buy Shares</span>
                    </a>
                    <a href="{{ route('payroll.compile') }}" class="flex flex-col items-center gap-2 p-4 rounded-[16px] hover:bg-indigo-50 transition group">
                        <div class="w-10 h-10 bg-indigo-100 rounded-[16px] flex items-center justify-center group-hover:bg-indigo-200 transition">
                            <span class="material-symbols-outlined text-indigo-600">receipt_long</span>
                        </div>
                        <span class="text-xs font-medium text-slate-700 text-center">Compile Payroll</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-[16px] hover:bg-slate-100 transition group">
                        <div class="w-10 h-10 bg-slate-200 rounded-[16px] flex items-center justify-center group-hover:bg-slate-300 transition">
                            <span class="material-symbols-outlined text-slate-600">assessment</span>
                        </div>
                        <span class="text-xs font-medium text-slate-700 text-center">Reports</span>
                    </a>
                </div>
            </div>

            {{-- Recent Activity Feed --}}
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-[#0F172A]">Recent Activity</h3>
                </div>
                <div class="divide-y divide-slate-50 max-h-[360px] overflow-y-auto">
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
                                    $iconData = $eventIcons[$activity->event] ?? ['info', 'bg-slate-100 text-slate-600'];
                                @endphp
                                <div class="w-8 h-8 {{ $iconData[1] }} rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-sm">{{ $iconData[0] }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-slate-700 leading-snug">{{ $activity->description ?? $activity->event }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ $activity->user->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-slate-500">No recent activity.</div>
                    @endforelse
                </div>
            </div>

            {{-- Top Savers --}}
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#0F172A]">Top Savers</h3>
                    <a href="{{ route('savings.accounts') }}" class="text-xs text-blue-600 hover:underline">View All &rarr;</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse ($topSavers as $index => $account)
                        <a href="{{ route('members.show', $account->member) }}" class="px-6 py-3 flex items-center justify-between hover:bg-slate-50 transition block">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-400 w-5 text-center">#{{ $index + 1 }}</span>
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-xs font-bold text-green-700">
                                    {{ strtoupper(substr($account->member->first_name, 0, 1) . substr($account->member->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-[#0F172A]">{{ $account->member->first_name }} {{ $account->member->last_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $account->member->staff_id_display }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-green-600">₦{{ number_format($account->balance) }}</span>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-slate-500">No savings accounts with balances yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Members --}}
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#0F172A]">Recent Members</h3>
                    <a href="{{ route('members.index') }}" class="text-xs text-blue-600 hover:underline">View All &rarr;</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse ($recentMembers as $member)
                        <a href="{{ route('members.show', $member) }}" class="px-6 py-3 flex items-center justify-between hover:bg-slate-50 transition block">
                            <div class="flex items-center gap-3">
                                @if ($member->user && $member->user->avatar_url)
                                    <img src="{{ $member->user->avatar_url }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-xs font-bold text-slate-600">
                                        {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-[#0F172A]">{{ $member->first_name }} {{ $member->last_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $member->staff_id_display }} &middot; {{ $member->region->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $member->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-slate-500">No members yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Loans --}}
            <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#0F172A]">Recent Loans</h3>
                    <a href="{{ route('loans.index') }}" class="text-xs text-blue-600 hover:underline">View All &rarr;</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse ($recentLoans as $loan)
                        <a href="{{ route('loans.show', $loan) }}" class="px-6 py-3 flex items-center justify-between hover:bg-slate-50 transition block">
                            <div>
                                <p class="text-sm font-medium text-[#0F172A]">{{ $loan->member->first_name ?? '' }} {{ $loan->member->last_name ?? '' }}</p>
                                <p class="text-xs text-slate-500 font-mono">{{ $loan->loan_number }} &middot; ₦{{ number_format($loan->amount) }}</p>
                            </div>
                            @php
                                $loanColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'approved' => 'bg-purple-100 text-purple-700',
                                    'disbursed' => 'bg-orange-100 text-orange-700',
                                    'repaying' => 'bg-blue-100 text-blue-700',
                                    'completed' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'defaulted' => 'bg-slate-200 text-slate-700',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ $loanColors[$loan->status] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-slate-500">No loans yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Loan Status + Region Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-[16px] shadow-sm p-6 border border-slate-200">
                <h3 class="text-sm font-semibold text-[#0F172A] mb-4">Loan Portfolio</h3>
                <div style="position: relative; height: 200px;">
                    <canvas id="loanStatusChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-[16px] shadow-sm p-6 border border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-[#0F172A]">Members by Region</h3>
                    <a href="{{ route('reports.index') }}" class="text-xs text-blue-600 hover:underline">Reports &rarr;</a>
                </div>
                <div class="space-y-3">
                    @forelse ($regionStats as $stat)
                        <div class="flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-slate-700 font-medium">{{ $stat->region->name ?? 'Unknown' }}</span>
                                    <span class="text-xs text-slate-500">{{ $stat->total }} members</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $totalMembers > 0 ? round(($stat->total / $totalMembers) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 text-center py-4">No region data.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Action Button --}}
    <div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50">
        <template x-teleport="body">
            <div x-show="open" x-cloak class="fixed inset-0 z-40" @click="open = false"></div>
        </template>
        <div x-show="open" x-cloak x-transition class="absolute bottom-16 right-0 flex flex-col items-end gap-2 z-50">
            <a href="{{ route('members.create') }}" @click="open = false" class="flex items-center gap-2 bg-white border border-slate-200 shadow-lg rounded-[10px] px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition whitespace-nowrap">
                <span class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600 text-sm">person_add</span>
                </span>
                New Member
            </a>
            <a href="{{ route('savings.deposit') }}" @click="open = false" class="flex items-center gap-2 bg-white border border-slate-200 shadow-lg rounded-[10px] px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition whitespace-nowrap">
                <span class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-emerald-600 text-sm">savings</span>
                </span>
                Post Deposit
            </a>
            <a href="{{ route('loans.create') }}" @click="open = false" class="flex items-center gap-2 bg-white border border-slate-200 shadow-lg rounded-[10px] px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition whitespace-nowrap">
                <span class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-amber-600 text-sm">request_quote</span>
                </span>
                Approve Loan
            </a>
        </div>
        <button @click="open = !open"
                class="relative z-50 w-12 h-12 rounded-full bg-[#0F172A] text-white shadow-lg hover:bg-slate-800 transition flex items-center justify-center"
                :class="{'rotate-45': open}">
            <span class="material-symbols-outlined text-2xl transition-transform duration-200" :class="open ? 'rotate-45' : ''">add</span>
        </button>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Savings sparkline
            const sparkCanvas = document.getElementById('savingsSparkline');
            if (sparkCanvas) {
                const sparkData = {!! $savingsSparklineData !!};
                new Chart(sparkCanvas, {
                    type: 'line',
                    data: {
                        labels: Array(sparkData.length).fill(''),
                        datasets: [{
                            data: sparkData,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 1.5,
                            pointRadius: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        scales: { x: { display: false }, y: { display: false } },
                        elements: { point: { radius: 0 } }
                    }
                });
            }

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
