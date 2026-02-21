@props([
    'terms' => [],
    'view' => 'checkboxes',
    'moreLess' => false,
    'moreLessCount' => 5,
    'hasSearch' => false,
    'taxonomy' => '',
])

@php
    $totalTerms = count($terms);
    $showToggle = $moreLess && $totalTerms > $moreLessCount;
    $hiddenCount = $totalTerms - $moreLessCount;
    $termsJson = json_encode(array_map(fn($t) => strtolower($t['label']), $terms));
@endphp

{{--
    Note: This component relies on parent's Alpine scope for:
    - searchQuery (when hasSearch is true)
    - expanded state is self-contained
--}}
<div
    x-data="{
        expanded: false,
        terms: {{ $termsJson }},
        isVisible(index) {
            // Check search filter
            if ({{ $hasSearch ? 'true' : 'false' }} && searchQuery && searchQuery.length > 0) {
                if (!this.terms[index].includes(searchQuery.toLowerCase())) {
                    return false;
                }
            }
            // Check more/less
            if ({{ $showToggle ? 'true' : 'false' }} && !this.expanded && index >= {{ $moreLessCount }}) {
                return false;
            }
            return true;
        }
    }"
    class="space-y-1"
>
    @if ($view === 'range')
        {{-- Range slider filter --}}
        <x-filters.types.range :taxonomy="$taxonomy" />
    @else
        {{-- List-based filters (checkbox, radio, rating) --}}
        <ul class="space-y-1">
            @foreach ($terms as $index => $term)
                <li x-show="isVisible({{ $index }})" x-cloak>
                    @switch($view)
                        @case('checkboxes')
                            <x-filters.types.checkbox :term="$term" :taxonomy="$taxonomy" />
                            @break

                        @case('radio')
                            <x-filters.types.radio :term="$term" :taxonomy="$taxonomy" />
                            @break

                        @case('rating')
                            <x-filters.types.rating :term="$term" :taxonomy="$taxonomy" />
                            @break

                        @default
                            <x-filters.types.checkbox :term="$term" :taxonomy="$taxonomy" />
                    @endswitch
                </li>
            @endforeach
        </ul>

        {{-- More/Less toggle --}}
        @if ($showToggle)
            <button
                type="button"
                class="text-sm text-red-600 hover:text-red-700 underline"
                @click="expanded = !expanded"
                x-show="!searchQuery || searchQuery.length === 0"
            >
                <span x-show="!expanded">
                    {{ __('Toon meer', 'sage') }} ({{ $hiddenCount }})
                </span>
                <span x-show="expanded" x-cloak>
                    {{ __('Toon minder', 'sage') }}
                </span>
            </button>
        @endif
    @endif
</div>
