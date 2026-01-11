@props([
    'taxonomy' => '',
    'filter' => [],
])

{{--
    Range/Slider filter type component.
    For numeric range filters (e.g., price range).
    Uses min/max input fields with an apply button.

    For a full slider UI, consider integrating noUiSlider or similar.
--}}

@php
    // Try to extract min/max from terms or filter settings
    $terms = $filter['terms'] ?? [];
    $settings = $filter['settings'] ?? [];

    // Default values
    $min = 0;
    $max = 1000;
    $step = 1;
    $prefix = '';
    $suffix = '';

    // Check if this is a price filter
    if (str_contains($taxonomy, 'price')) {
        $prefix = get_woocommerce_currency_symbol();
    }

    // Try to get current values from URL
    $currentMin = $_GET['min_' . $taxonomy] ?? $_GET['min_price'] ?? null;
    $currentMax = $_GET['max_' . $taxonomy] ?? $_GET['max_price'] ?? null;

    // Generate a unique ID
    $inputIdMin = 'filter-range-min-' . $taxonomy;
    $inputIdMax = 'filter-range-max-' . $taxonomy;
@endphp

<div
    x-data="{
        minValue: {{ $currentMin ?? $min }},
        maxValue: {{ $currentMax ?? $max }},
        min: {{ $min }},
        max: {{ $max }},
        step: {{ $step }},
        applyRange() {
            const url = new URL(window.location.href);
            @if (str_contains($taxonomy, 'price'))
                url.searchParams.set('min_price', this.minValue);
                url.searchParams.set('max_price', this.maxValue);
            @else
                url.searchParams.set('min_{{ $taxonomy }}', this.minValue);
                url.searchParams.set('max_{{ $taxonomy }}', this.maxValue);
            @endif
            $dispatch('filter-apply', { url: url.toString() });
        }
    }"
    class="py-2"
>
    {{-- Visual range bar (placeholder for future slider) --}}
    <div class="h-2 bg-gray-200 rounded-full mb-4 relative">
        <div class="absolute h-full bg-red-600 rounded-full" style="left: 0%; right: 0%;"></div>
    </div>

    {{-- Min/Max inputs --}}
    <div class="flex items-center gap-2 mb-3">
        <div class="flex-1">
            <label for="{{ $inputIdMin }}" class="sr-only">{{ __('Minimum', 'sage') }}</label>
            <div class="relative">
                @if ($prefix)
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">{{ $prefix }}</span>
                @endif
                <input
                    type="number"
                    id="{{ $inputIdMin }}"
                    x-model.number="minValue"
                    :min="min"
                    :max="maxValue"
                    :step="step"
                    class="w-full py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-red-500 focus:border-red-500 {{ $prefix ? 'pl-7 pr-3' : 'px-3' }}"
                    placeholder="{{ __('Min', 'sage') }}"
                />
            </div>
        </div>

        <span class="text-gray-400 text-sm">{{ __('tot', 'sage') }}</span>

        <div class="flex-1">
            <label for="{{ $inputIdMax }}" class="sr-only">{{ __('Maximum', 'sage') }}</label>
            <div class="relative">
                @if ($prefix)
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">{{ $prefix }}</span>
                @endif
                <input
                    type="number"
                    id="{{ $inputIdMax }}"
                    x-model.number="maxValue"
                    :min="minValue"
                    :max="max"
                    :step="step"
                    class="w-full py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-red-500 focus:border-red-500 {{ $prefix ? 'pl-7 pr-3' : 'px-3' }}"
                    placeholder="{{ __('Max', 'sage') }}"
                />
            </div>
        </div>
    </div>

    {{-- Apply button --}}
    <button
        type="button"
        @click="applyRange()"
        class="w-full px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
    >
        {{ __('Toepassen', 'sage') }}
    </button>
</div>
