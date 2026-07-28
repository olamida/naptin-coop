@props(['steps' => [], 'current' => 1])

@php
    $totalSteps = count($steps);
@endphp

<div class="w-full">
    {{-- Desktop --}}
    <div class="hidden sm:flex items-center justify-between">
        @foreach ($steps as $index => $step)
            @php
                $stepNum = $index + 1;
                $isCompleted = $stepNum < $current;
                $isCurrent = $stepNum === $current;
                $isFuture = $stepNum > $current;
            @endphp

            <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-all
                        {{ $isCompleted ? 'bg-emerald-500 text-white' : '' }}
                        {{ $isCurrent ? 'bg-blue-600 text-white ring-4 ring-blue-100' : '' }}
                        {{ $isFuture ? 'bg-gray-200 text-gray-500' : '' }}">
                        @if ($isCompleted)
                            <span class="material-symbols-outlined text-lg">check</span>
                        @elseif (isset($step['icon']))
                            <span class="material-symbols-outlined text-lg">{{ $step['icon'] }}</span>
                        @else
                            {{ $stepNum }}
                        @endif
                    </div>
                    <span class="mt-2 text-xs font-medium text-center whitespace-nowrap
                        {{ $isCompleted ? 'text-emerald-600' : '' }}
                        {{ $isCurrent ? 'text-blue-600 font-semibold' : '' }}
                        {{ $isFuture ? 'text-gray-400' : '' }}">
                        {{ $step['label'] }}
                    </span>
                </div>

                @if (!$loop->last)
                    <div class="flex-1 mx-2 mt-[-1.25rem]">
                        <div class="h-0.5 w-full rounded {{ $isCompleted ? 'bg-emerald-400' : 'bg-gray-200' }}"></div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Mobile: vertical --}}
    <div class="sm:hidden space-y-0">
        @foreach ($steps as $index => $step)
            @php
                $stepNum = $index + 1;
                $isCompleted = $stepNum < $current;
                $isCurrent = $stepNum === $current;
                $isFuture = $stepNum > $current;
            @endphp

            <div class="flex items-start gap-3 {{ !$loop->last ? 'pb-4' : '' }}">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold shrink-0
                        {{ $isCompleted ? 'bg-emerald-500 text-white' : '' }}
                        {{ $isCurrent ? 'bg-blue-600 text-white ring-4 ring-blue-100' : '' }}
                        {{ $isFuture ? 'bg-gray-200 text-gray-500' : '' }}">
                        @if ($isCompleted)
                            <span class="material-symbols-outlined text-sm">check</span>
                        @else
                            {{ $stepNum }}
                        @endif
                    </div>
                    @if (!$loop->last)
                        <div class="w-0.5 h-4 mt-1 {{ $isCompleted ? 'bg-emerald-400' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
                <div class="pt-0.5">
                    <span class="text-sm font-medium
                        {{ $isCompleted ? 'text-emerald-600' : '' }}
                        {{ $isCurrent ? 'text-blue-600 font-semibold' : '' }}
                        {{ $isFuture ? 'text-gray-400' : '' }}">
                        {{ $step['label'] }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>
