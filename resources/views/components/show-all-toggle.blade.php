@php
    $perPage = request()->input('per_page');
@endphp

@if($perPage)
    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
        <p class="text-xs text-gray-400">
            {{ $perPage === 'all' ? 'Showing all records' : 'Showing paginated results' }}
        </p>
        @if($perPage === 'all')
            <a href="{{ strtok(request()->url(), '?') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                <span class="material-symbols-outlined text-[14px] align-middle">unfold_less</span>
                Show Paginated
            </a>
        @else
            <a href="{{ request()->url() . '?' . http_build_query(array_merge(request()->query(), ['per_page' => 'all'])) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                <span class="material-symbols-outlined text-[14px] align-middle">unfold_more</span>
                Show All
            </a>
        @endif
    </div>
@endif
