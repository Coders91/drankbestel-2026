@props([
    'brand' => [],
    'showName' => true,
])

@php
    $name = $brand['name'] ?? '';
    $url = $brand['url'] ?? null;
    $thumbnailUrl = $brand['thumbnail_url'] ?? null;
    $isLinkable = $brand['is_linkable'] ?? false;
    $firstLetter = mb_strtoupper(mb_substr(trim($name), 0, 1, 'UTF-8'));

    // Generate a consistent color based on the first letter for variety
    $letterColors = [
        'A' => 'from-red-500/10 to-red-600/5',
        'B' => 'from-amber-500/10 to-amber-600/5',
        'C' => 'from-emerald-500/10 to-emerald-600/5',
        'D' => 'from-cyan-500/10 to-cyan-600/5',
        'E' => 'from-blue-500/10 to-blue-600/5',
        'F' => 'from-violet-500/10 to-violet-600/5',
        'G' => 'from-pink-500/10 to-pink-600/5',
        'H' => 'from-rose-500/10 to-rose-600/5',
        'I' => 'from-orange-500/10 to-orange-600/5',
        'J' => 'from-teal-500/10 to-teal-600/5',
        'K' => 'from-indigo-500/10 to-indigo-600/5',
        'L' => 'from-fuchsia-500/10 to-fuchsia-600/5',
        'M' => 'from-red-500/10 to-red-600/5',
        'N' => 'from-amber-500/10 to-amber-600/5',
        'O' => 'from-emerald-500/10 to-emerald-600/5',
        'P' => 'from-cyan-500/10 to-cyan-600/5',
        'Q' => 'from-blue-500/10 to-blue-600/5',
        'R' => 'from-violet-500/10 to-violet-600/5',
        'S' => 'from-pink-500/10 to-pink-600/5',
        'T' => 'from-rose-500/10 to-rose-600/5',
        'U' => 'from-orange-500/10 to-orange-600/5',
        'V' => 'from-teal-500/10 to-teal-600/5',
        'W' => 'from-indigo-500/10 to-indigo-600/5',
        'X' => 'from-fuchsia-500/10 to-fuchsia-600/5',
        'Y' => 'from-red-500/10 to-red-600/5',
        'Z' => 'from-amber-500/10 to-amber-600/5',
    ];
    $avatarGradient = $letterColors[$firstLetter] ?? 'from-gray-500/10 to-gray-600/5';
@endphp

@if($isLinkable && $url)
    <a
        href="{{ $url }}"
        {{ $attributes->merge([
            'class' => 'group block bg-white rounded-xl border border-gray-200 p-4 lg:p-5 transition-all duration-300 hover:border-red-200 hover:shadow-md hover:shadow-red-500/5 hover:-translate-y-0.5'
        ]) }}
        title="{{ $name }}"
    >
        <div class="flex flex-col items-center gap-3">
            {{-- Logo or Avatar --}}
            <div class="relative w-full aspect-[3/2] flex items-center justify-center overflow-hidden rounded-lg bg-gray-50 group-hover:bg-gray-100/50 transition-colors">
                @if($thumbnailUrl)
                    <img
                        src="{{ $thumbnailUrl }}"
                        alt="{{ $name }}"
                        class="max-w-full max-h-full w-auto h-auto object-contain p-2 grayscale opacity-70 transition-all duration-300 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-105"
                        loading="lazy"
                        decoding="async"
                    />
                @else
                    {{-- First letter avatar with subtle gradient --}}
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br {{ $avatarGradient }}">
                        <span class="text-3xl lg:text-4xl font-heading font-semibold text-gray-400 group-hover:text-red-600 transition-colors duration-300">
                            {{ $firstLetter }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Brand name --}}
            @if($showName)
                <span class="text-sm font-medium text-gray-700 text-center leading-tight group-hover:text-gray-900 transition-colors line-clamp-2">
                    {{ $name }}
                </span>
            @endif
        </div>

        {{-- Subtle hover indicator --}}
        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-red-600 rounded-full transition-all duration-300 group-hover:w-8"></div>
    </a>
@else
    {{-- Non-linkable brand (display only) --}}
    <div
        {{ $attributes->merge([
            'class' => 'block bg-white rounded-xl border border-gray-200 p-4 lg:p-5'
        ]) }}
    >
        <div class="flex flex-col items-center gap-3">
            {{-- Logo or Avatar --}}
            <div class="relative w-full aspect-[3/2] flex items-center justify-center overflow-hidden rounded-lg bg-gray-50">
                @if($thumbnailUrl)
                    <img
                        src="{{ $thumbnailUrl }}"
                        alt="{{ $name }}"
                        class="max-w-full max-h-full w-auto h-auto object-contain p-2 grayscale opacity-60"
                        loading="lazy"
                        decoding="async"
                    />
                @else
                    {{-- First letter avatar --}}
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br {{ $avatarGradient }}">
                        <span class="text-3xl lg:text-4xl font-heading font-semibold text-gray-300">
                            {{ $firstLetter }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Brand name --}}
            @if($showName)
                <span class="text-sm font-medium text-gray-500 text-center leading-tight line-clamp-2">
                    {{ $name }}
                </span>
            @endif
        </div>
    </div>
@endif
