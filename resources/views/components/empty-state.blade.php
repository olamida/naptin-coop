@props([
    'icon' => 'inbox',
    'title' => 'No data found',
    'message' => null,
    'actionUrl' => null,
    'actionLabel' => null,
])

<div class="text-center py-12">
    <span class="material-symbols-outlined text-5xl text-slate-300">{{ $icon }}</span>
    <h3 class="text-lg font-medium text-[#0F172A] mt-4">{{ $title }}</h3>
    @if ($message)
        <p class="text-sm text-slate-500 mt-1">{{ $message }}</p>
    @endif
    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="mt-4 inline-flex items-center px-4 py-2 bg-[#0F172A] text-white text-sm font-medium rounded-[10px] hover:bg-slate-800 transition">
            {{ $actionLabel }}
        </a>
    @endif
</div>
