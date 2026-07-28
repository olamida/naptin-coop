@props([
    'status',
    'type' => 'default',
])

@php
    $statusConfig = match($status) {
        'active', 'completed', 'approved', 'accepted', 'paid' => [
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot' => 'bg-emerald-500',
        ],
        'pending', 'draft', 'compiled' => [
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot' => 'bg-amber-500',
        ],
        'rejected', 'declined', 'defaulted' => [
            'class' => 'bg-red-50 text-red-700 border-red-200',
            'dot' => 'bg-red-500',
        ],
        'disbursed', 'repaying', 'active', 'deducted' => [
            'class' => 'bg-blue-50 text-blue-700 border-blue-200',
            'dot' => 'bg-blue-500',
        ],
        'inactive', 'retired', 'suspended' => [
            'class' => 'bg-gray-50 text-gray-700 border-gray-200',
            'dot' => 'bg-gray-500',
        ],
        default => [
            'class' => 'bg-gray-50 text-gray-700 border-gray-200',
            'dot' => 'bg-gray-500',
        ],
    };
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusConfig['class'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
    {{ ucfirst($status) }}
</span>
