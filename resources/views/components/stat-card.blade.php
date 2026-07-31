@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'blue',
    'subtitle' => null,
    'class' => '',
])

@php
    $colorClasses = match($color) {
        'blue' => 'bg-blue-50 text-blue-600 border-blue-100',
        'green' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'red' => 'bg-red-50 text-red-600 border-red-100',
        'yellow' => 'bg-amber-50 text-amber-600 border-amber-100',
        'purple' => 'bg-purple-50 text-purple-600 border-purple-100',
        'indigo' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
        default => 'bg-blue-50 text-blue-600 border-blue-100',
    };
    $iconColorClasses = match($color) {
        'blue' => 'bg-blue-100',
        'green' => 'bg-emerald-100',
        'red' => 'bg-red-100',
        'yellow' => 'bg-amber-100',
        'purple' => 'bg-purple-100',
        'indigo' => 'bg-indigo-100',
        default => 'bg-blue-100',
    };
@endphp

<div class="stat-card bg-white rounded-[16px] border border-slate-200 p-5 {{ $class }}">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ $title }}</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-1">{{ $value }}</p>
            @if ($subtitle)
                <p class="text-xs text-slate-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="w-12 h-12 rounded-[16px] {{ $iconColorClasses }} flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-xl {{ $colorClasses }}">{{ $icon }}</span>
            </div>
        @endif
    </div>
</div>
