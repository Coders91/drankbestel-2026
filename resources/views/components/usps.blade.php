@props([
    'usps' => [],
    'variant' => 'grid',
    'columns' => 4,
    'showSubtitle' => true,
])

@php
    $gridCols = match ($columns) {
        2 => 'grid-cols-2',
        3 => 'grid-cols-2 lg:grid-cols-3',
        default => 'grid-cols-2 lg:grid-cols-4',
    };
@endphp

@if (count($usps) > 0)
    {{-- Grid Variant: Simple centered items --}}
    @if ($variant === 'grid')
        <div {{ $attributes->merge(['class' => "grid {$gridCols} gap-4"]) }}>
            @foreach ($usps as $usp)
                <div class="flex flex-col items-center gap-3 p-4">
                    @if (isset($usp['icon']))
                        @svg('resources.images.icons.' . $usp['icon'], 'size-6 text-red-600')
                    @endif
                    <div class="text-center">
                        <span class="block text-sm font-medium text-gray-800">{{ $usp['title'] }}</span>
                        @if ($showSubtitle && isset($usp['subtitle']))
                            <span class="text-xs text-gray-500">{{ $usp['subtitle'] }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    {{-- Boxed Variant: Gray background with rounded container --}}
    @elseif ($variant === 'boxed')
        <div {{ $attributes->merge(['class' => "rounded-xl p-6"]) }}>
            <div class="container p-0 grid {{ $gridCols }} gap-4">
                @foreach ($usps as $usp)
                    <div class="flex flex-col items-center gap-3 p-4">
                        @if (isset($usp['icon']))
                            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-600 text-white">
                                @svg('resources.images.icons.' . $usp['icon'], 'size-6')
                            </div>
                        @endif
                        <div class="text-center">
                            <span class="block text-sm font-medium text-gray-800">{{ $usp['title'] }}</span>
                            @if ($showSubtitle && isset($usp['subtitle']))
                                <span class="text-xs text-gray-500">{{ $usp['subtitle'] }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    {{-- Horizontal Variant: Icon in bordered circle + text side by side --}}
    @elseif ($variant === 'horizontal')
        <div {{ $attributes->merge(['class' => "grid {$gridCols} gap-4 p-4 bg-gray-50 rounded-xl"]) }}>
            @foreach ($usps as $usp)
                <div class="flex items-center gap-3">
                    @if (isset($usp['icon']))
                        @svg('resources.images.icons.' . $usp['icon'], 'size-6 text-red-600 shrink-0')
                    @endif
                    <div>
                        <span class="block text-sm font-semibold text-gray-900">{{ $usp['title'] }}</span>
                        @if ($showSubtitle && isset($usp['subtitle']))
                            <span class="text-xs text-gray-500">{{ $usp['subtitle'] }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    {{-- Minimal Variant: Simple inline text with small icon --}}
    @elseif ($variant === 'minimal')
        <div {{ $attributes->merge(['class' => "flex flex-wrap justify-center gap-6"]) }}>
            @foreach ($usps as $usp)
                <div class="flex items-center gap-2">
                    @if (isset($usp['icon']))
                        @svg('resources.images.icons.' . $usp['icon'], 'size-5 text-red-600')
                    @endif
                    <span class="text-sm font-medium text-gray-700">{{ $usp['title'] }}</span>
                </div>
            @endforeach
        </div>
    @endif
@endif
