@props(['items' => []])

@if (count($items) > 0)
    <nav class="flex items-center gap-1.5 text-sm text-gray-500 mb-2">
        <a href="{{ route('dashboard') }}" class="hover:text-gray-700 transition">
            <span class="material-symbols-outlined text-[16px] align-middle">home</span>
        </a>
        @foreach ($items as $index => $item)
            <span class="material-symbols-outlined text-[14px] text-gray-400">chevron_right</span>
            @if (isset($item['url']))
                <a href="{{ $item['url'] }}" class="hover:text-gray-700 transition">{{ $item['label'] }}</a>
            @else
                <span class="text-gray-700 font-medium">{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
    <hr class="border-gray-200 mb-6">
@endif
