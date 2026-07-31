<x-portal-layout title="Notifications">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-[#0F172A]">Notifications</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $unreadCount }} unread notification{{ $unreadCount !== 1 ? 's' : '' }}</p>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('portal.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-[16px] shadow-sm border border-slate-200 overflow-hidden">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $icon = match($data['type'] ?? '') {
                        'loan_status' => 'account_balance',
                        'guarantor_request' => 'group_add',
                        'withdrawal_status' => 'savings',
                        'admin_password_reset' => 'lock_reset',
                        'deposit_recorded' => 'savings',
                        'share_purchased' => 'trending_up',
                        'dividend_declared' => 'diversity_3',
                        'payroll_compiled' => 'payments',
                        'member_registered' => 'person_add',
                        'broadcast' => 'campaign',
                        default => 'notifications',
                    };
                    $iconBg = match($data['type'] ?? '') {
                        'loan_status' => match($data['new_status'] ?? '') {
                            'approved' => 'bg-green-100 text-green-600',
                            'rejected' => 'bg-red-100 text-red-600',
                            'disbursed' => 'bg-blue-100 text-blue-600',
                            'completed' => 'bg-emerald-100 text-emerald-600',
                            default => 'bg-slate-100 text-slate-600',
                        },
                        'guarantor_request' => 'bg-yellow-100 text-yellow-600',
                        'withdrawal_status' => match($data['new_status'] ?? '') {
                            'completed' => 'bg-green-100 text-green-600',
                            'rejected' => 'bg-red-100 text-red-600',
                            default => 'bg-blue-100 text-blue-600',
                        },
                        'admin_password_reset' => 'bg-orange-100 text-orange-600',
                        'deposit_recorded' => 'bg-green-100 text-green-600',
                        'share_purchased' => 'bg-purple-100 text-purple-600',
                        'dividend_declared' => 'bg-indigo-100 text-indigo-600',
                        'payroll_compiled' => 'bg-blue-100 text-blue-600',
                        'member_registered' => 'bg-blue-100 text-blue-600',
                        'broadcast' => match($data['priority'] ?? '') {
                            'urgent' => 'bg-red-100 text-red-600',
                            'high' => 'bg-orange-100 text-orange-600',
                            default => 'bg-blue-100 text-blue-600',
                        },
                        default => 'bg-slate-100 text-slate-600',
                    };
                    $actionUrl = \App\Support\NotificationLinks::actionUrl($data, 'portal');
                @endphp
                @if ($actionUrl)
                    <a href="{{ $actionUrl }}" class="flex items-start gap-4 px-5 py-4 border-b border-slate-200 last:border-0 transition {{ $isUnread ? 'notif-unread' : '' }} hover:bg-slate-50">
                @else
                    <div class="flex items-start gap-4 px-5 py-4 border-b border-slate-200 last:border-0 {{ $isUnread ? 'notif-unread' : '' }}">
                @endif
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 {{ $iconBg }}">
                        <span class="material-symbols-outlined text-lg">{{ $icon }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm {{ $isUnread ? 'text-[#0F172A] font-medium' : 'text-slate-700' }}">
                            {{ $data['message'] ?? 'Notification' }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if ($isUnread)
                            <form method="POST" action="{{ route('portal.notifications.read', $notification->id) }}" @click.prevent.stop>
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition" title="Mark as read">
                                    Mark read
                                </button>
                            </form>
                        @else
                            <span class="w-2 h-2 rounded-full bg-slate-300" title="Read"></span>
                        @endif
                    </div>
                @if ($actionUrl)
                    </a>
                @else
                    </div>
                @endif
            @empty
                <div class="px-5">
                    <x-empty-state icon="notifications_off" title="All caught up"
                        message="Loan status updates, guarantor requests and savings confirmations will appear here."
                        actionUrl="{{ route('portal.products') }}" actionLabel="Browse the shop" />
                </div>
            @endforelse
        </div>

        {{ $notifications->links() }}
    </div>
</x-portal-layout>
