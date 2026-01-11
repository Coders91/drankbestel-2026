@props([
    'brands' => null,
    'limit' => 6,
])

<div {{ $attributes }}>
    <h3 class="text-sm font-semibold text-gray-900 mb-4">
        {{ __('Top Merken', 'sage') }}
    </h3>

    @if ($brands && $brands->count() > 0)
        <div class="grid grid-cols-2 gap-3">
            @foreach ($brands->take($limit) as $brand)
                <a
                    href="{{ $brand['url'] }}"
                    class="group flex items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-red-50 transition-colors"
                    title="{{ $brand['name'] }}"
                >
                    @if ($brand['image'])
                        <img
                            src="{{ $brand['image'] }}"
                            alt="{{ $brand['name'] }}"
                            class="max-h-8 w-auto object-contain grayscale group-hover:grayscale-0 transition-all"
                            loading="lazy"
                        />
                    @else
                        <span class="text-xs font-medium text-gray-600 group-hover:text-red-600 text-center transition-colors">
                            {{ $brand['name'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- View all brands link --}}
        <a
            href="{{ home_url('/merken/') }}"
            class="inline-flex items-center gap-1.5 mt-4 text-sm font-medium text-gray-500 hover:text-red-600 transition-colors"
        >
            <span>{{ __('Alle merken', 'sage') }}</span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    @else
        <p class="text-sm text-gray-500">
            {{ __('Geen merken gevonden', 'sage') }}
        </p>
    @endif
</div>
