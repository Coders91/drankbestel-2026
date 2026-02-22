@props([
    'category' => [],
])

@php
    $name = $category['name'] ?? '';
    $url = $category['url'] ?? '#';
    $imageUrl = $category['image_url'] ?? null;
    $count = $category['count'] ?? 0;
    $firstLetter = mb_strtoupper(mb_substr(trim($name), 0, 1, 'UTF-8'));

    // Generate a consistent color based on the first letter
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

<a
    href="{{ $url }}"
    {{ $attributes->merge([
        'class' => 'group block bg-white rounded-xl border border-gray-200 p-3 transition-all duration-300 hover:border-red-200 hover:shadow-md hover:shadow-red-500/5 hover:-translate-y-0.5'
    ]) }}
    title="{{ $name }}"
>
    <div class="flex flex-col items-center gap-2">
        {{-- Image or Avatar --}}
        <div class="relative w-full aspect-[4/3] flex items-center justify-center overflow-hidden rounded bg-gray-50 group-hover:bg-gray-100/50 transition-colors">
            @if($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $name }}"
                    class="max-w-full max-h-full w-auto h-auto object-contain p-1.5 grayscale opacity-70 transition-all duration-300 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-105"
                    loading="lazy"
                    decoding="async"
                />
            @else
                {{-- First letter avatar with subtle gradient --}}
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br {{ $avatarGradient }}">
                    <span class="text-2xl font-heading font-semibold text-gray-400 group-hover:text-red-600 transition-colors duration-300">
                        {{ $firstLetter }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Category name --}}
        <span class="text-xs font-medium text-gray-700 text-center leading-tight group-hover:text-gray-900 transition-colors line-clamp-2">
            {{ $name }}
        </span>
    </div>
</a>
