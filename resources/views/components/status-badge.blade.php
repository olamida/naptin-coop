@props([
    'status',
    'type' => 'default',
    'dot' => true,
])

@php
    $color = match(true) {
        $status instanceof \App\Enums\GuarantorStatus ||
        $status instanceof \App\Enums\LoanStatus ||
        $status instanceof \App\Enums\PayrollStatus ||
        $status instanceof \App\Enums\MemberStatus ||
        $status instanceof \App\Enums\SavingsTransactionType ||
        $status instanceof \App\Enums\ShareTransactionType ||
        $status instanceof \App\Enums\PaymentMethod ||
        $status instanceof \App\Enums\LoanType
            => $status->color(),
        is_string($status) => match($status) {
            'active', 'completed', 'approved', 'accepted', 'paid', 'success' => 'success',
            'pending', 'draft', 'compiled', 'warning' => 'warning',
            'rejected', 'declined', 'defaulted', 'danger' => 'danger',
            'disbursed', 'repaying', 'deducted', 'blue' => 'blue',
            'guarantor_pending', 'indigo' => 'indigo',
            'inactive', 'retired', 'suspended', 'gray' => 'gray',
            'emerald' => 'emerald',
            'purple' => 'purple',
            default => 'gray',
        },
        default => 'gray',
    };

    $style = match($color) {
        'success' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
        'warning' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
        'danger' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'dot' => 'bg-red-500'],
        'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500'],
        'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'dot' => 'bg-indigo-500'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
        'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'dot' => 'bg-purple-500'],
        'gray' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'dot' => 'bg-gray-500'],
        default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'dot' => 'bg-gray-500'],
    };

    $label = match(true) {
        $status instanceof \BackedEnum => $status->label(),
        default => ucfirst((string) $status),
    };
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $style['bg'] }} {{ $style['text'] }} {{ $style['border'] }}">
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
    @endif
    {{ $label }}
</span>
