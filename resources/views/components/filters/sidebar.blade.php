@props([
    'filters' => [],
    'activeCount' => 0,
    'selectedChips' => [],
    'resetUrl' => '',
    'moreLessCount' => 5,
])
<div class="space-y-4">
  {{-- Active filter count and reset --}}
    @if ($activeCount > 0)
        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <a
                href="{{ $resetUrl }}"
                class="text-sm text-red-600 hover:text-red-700 underline"
                @click.prevent="applyFilter('{{ $resetUrl }}')"
            >
                {{ __('Wis filters', 'sage') }}
            </a>
        </div>

        {{-- Selected filter chips --}}
        @if (!empty($selectedChips))
            <div class="flex flex-wrap gap-2 pb-4 border-b border-gray-200">
                @foreach ($selectedChips as $chip)
                    <a
                        href="{{ $chip['link'] }}"
                        @click.prevent="applyFilter('{{ $chip['link'] }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors"
                    >
                        @if (isset($chip['rating']))
                            <x-star-rating :rating="(int) $chip['rating']" size="xs" class="text-yellow-500" />
                            <span>{{ __('& hoger', 'sage') }}</span>
                        @else
                            <span>{{ $chip['name'] }}</span>
                        @endif
                        @svg('resources.images.icons.x-close', 'w-4 h-4')
                    </a>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Filter groups --}}
    @foreach ($filters as $taxonomy => $filter)
        @if (!empty($filter['terms']))
            <x-filters.group
                :filter="$filter"
                :taxonomy="$taxonomy"
                :more-less-count="$moreLessCount"
            />
        @endif
    @endforeach
</div>
