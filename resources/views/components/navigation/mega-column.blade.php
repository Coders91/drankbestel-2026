@props([
    'title' => '',
    'url' => '#',
    'items' => null,
    'limit' => 5,
])

<div {{ $attributes }}>
    {{-- Column Header --}}
    <a
        href="{{ $url }}"
        class="group flex items-center gap-2 mb-4"
    >
        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-red-600 transition-colors">
            {{ $title }}
        </h3>
        <svg
            class="w-3.5 h-3.5 text-gray-400 group-hover:text-red-600 group-hover:translate-x-0.5 transition-all"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
    </a>

    {{-- Items --}}
    @if ($items && $items->count() > 0)
        <ul class="space-y-2">
            @foreach ($items->take($limit) as $item)
                <li>
                    <a
                        href="{{ $item['url'] }}"
                        class="block text-sm text-gray-600 hover:text-red-600 transition-colors"
                    >
                        {{ $item['name'] }}
                    </a>
                </li>
            @endforeach

            @if ($items->count() > $limit)
                <li class="pt-1">
                    <a
                        href="{{ $url }}"
                        class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-red-600 transition-colors"
                    >
                        <span>{{ __('Alles bekijken', 'sage') }}</span>
                        <span class="text-gray-400">({{ $items->count() }})</span>
                    </a>
                </li>
            @endif
        </ul>
    @endif
</div>
