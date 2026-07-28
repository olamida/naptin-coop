@props([
    'icon' => 'inbox',
    'title' => 'No data found',
    'message' => null,
    'actionUrl' => null,
    'actionLabel' => null,
])

<div class="text-center py-12">
    <span class="material-symbols-outlined text-5xl text-gray-300">{{ $icon }}</span>
    <h3 class="text-lg font-medium text-gray-900 mt-4">{{ $title }}</h3>
    @if ($message)
        <p class="text-sm text-gray-500 mt-1">{{ $message }}</p>
    @endif
    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            {{ $actionLabel }}
        </a>
    @endif
</div>
