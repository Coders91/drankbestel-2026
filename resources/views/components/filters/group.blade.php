@props([
    'filter' => [],
    'taxonomy' => '',
    'moreLessCount' => 5,
])

@php
    $settings = $filter['settings'] ?? [];
    $isOpen = ($settings['collapse'] ?? 'no') !== 'yes';
    $view = $settings['view'] ?? 'checkboxes';
    $moreLess = ($settings['more_less'] ?? 'no') === 'yes';
    $hasSearch = ($settings['search'] ?? 'no') === 'yes';
    $tooltip = $settings['tooltip'] ?? '';
    $uniqueId = 'filter-' . $taxonomy . '-' . substr(md5($taxonomy), 0, 6);

    $terms = $filter['terms'] ?? [];
    $totalTerms = count($terms);
    $showToggle = $moreLess && $totalTerms > $moreLessCount;
    $hiddenCount = $totalTerms - $moreLessCount;
    $termsJson = json_encode(array_map(fn($t) => strtolower($t['label']), $terms));
@endphp

<div
    class="border-b border-gray-200 pb-5"
    x-data="{
        open: @js($isOpen),
        searchQuery: '',
        expanded: false,
        terms: {{ $termsJson }},
        isVisible(index) {
            // Check search filter first
            if (this.searchQuery && this.searchQuery.length > 0) {
                if (!this.terms[index].includes(this.searchQuery.toLowerCase())) {
                    return false;
                }
            }
            // Check more/less (only when not searching)
            if ({{ $showToggle ? 'true' : 'false' }} && !this.expanded && index >= {{ $moreLessCount }}) {
                // When searching, show all matching results
                if (this.searchQuery && this.searchQuery.length > 0) {
                    return true;
                }
                return false;
            }
            return true;
        }
    }"
>
    {{-- Filter header / accordion toggle --}}
    <button
        type="button"
        class="flex items-center justify-between w-full text-left py-2 group"
        @click="open = !open"
        :aria-expanded="open.toString()"
        aria-controls="{{ $uniqueId }}"
    >
        <span class="font-semibold text-gray-900">
            {{ $filter['label'] }}
            @if ($tooltip)
                <span class="ml-1 text-gray-400 text-sm" title="{{ $tooltip }}">
                    @svg('resources.images.icons.info-circle', 'inline-block w-4 h-4')
                </span>
            @endif
        </span>
        @svg('resources.images.icons.chevron-up', 'w-5 h-5 text-gray-400 transition-transform duration-200', [
            ':class' => "{ 'rotate-180': !open }"
        ])
    </button>

    {{-- Filter content --}}
    <div
        id="{{ $uniqueId }}"
        x-show="open"
        x-collapse.duration.200ms
    >
        {{-- Search field (if enabled) --}}
        @if ($hasSearch)
            <div class="pt-2 mb-2">
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="{{ __('Zoeken...', 'sage') }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-red-500 focus:border-red-500"
                />
            </div>
        @endif

        {{-- Filter items --}}
        <div class="space-y-1 pt-2">
            @if ($view === 'range')
                {{-- Range slider filter --}}
                <x-filters.types.range :taxonomy="$taxonomy" :filter="$filter" />
            @else
                {{-- List-based filters (checkbox, radio, rating) --}}
                <ul class="space-y-1 max-h-[400px] overflow-y-auto">
                    @foreach ($terms as $index => $term)
                        <li x-show="isVisible({{ $index }})">
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
                        class="mt-2 text-sm text-red-600 hover:text-red-700 underline"
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
    </div>
</div>
