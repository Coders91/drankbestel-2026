@props([
    'filters' => [],
    'activeCount' => 0,
    'selectedChips' => [],
    'resetUrl' => '',
    'totalResults' => 0,
    'moreLessCount' => 5,
])

<div
    x-data="{
        open: false,
        isDesktop: window.matchMedia('(min-width: 1024px)').matches,
        activeCount: {{ $activeCount }},
        totalResults: {{ $totalResults }}
    }"
    x-init="
        const mq = window.matchMedia('(min-width: 1024px)');
        mq.addEventListener('change', (e) => isDesktop = e.matches);
    "
    @keydown.escape.window="open = false"
    @filter-applied.window="open = false"
    @filter-counts-updated.window="activeCount = $event.detail.activeCount; totalResults = $event.detail.totalResults"
>
    {{-- Teleport fixed elements to body to escape z-[0] stacking context --}}
    <template x-teleport="body">
        {{-- Mobile trigger button (hidden on desktop) --}}
        <button
            x-show="!isDesktop"
            type="button"
            @click="open = true"
            class="fixed bottom-4 left-4 right-4 z-40 flex items-center justify-center gap-2 px-4 py-3 border border-white bg-gray-900 text-white font-semibold rounded-full shadow-lg hover:bg-gray-800 transition-colors"
            x-cloak
        >
            @svg('resources.images.icons.filter-lines', 'w-5 h-5')
            <span>{{ __('Filters', 'sage') }}</span>
            <span
                x-show="activeCount > 0"
                x-text="activeCount"
                class="flex items-center justify-center min-w-[1.5rem] h-6 px-1.5 text-xs font-bold bg-red-600 rounded-full"
            ></span>
        </button>
    </template>

    <template x-teleport="body">
        {{-- Mobile backdrop --}}
        <div
            x-show="!isDesktop && open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="open = false"
            class="fixed inset-0 bg-black/50 z-40"
            x-cloak
        ></div>
    </template>

    <template x-teleport="body">
        {{-- Bottom sheet container --}}
        <div
            x-show="!isDesktop && open"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="fixed inset-0 z-50 bg-white rounded-t-2xl flex flex-col"
            x-cloak
        >
            {{-- Handle bar --}}
            <div class="flex justify-center pt-3 pb-2">
                <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
            </div>

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 pb-3 border-b border-gray-200">
                <h2 class="text-lg font-bold">
                    {{ __('Filters', 'sage') }}
                    <span x-show="activeCount > 0" class="text-sm font-normal text-gray-500">(<span x-text="activeCount"></span>)</span>
                </h2>
                <button
                    type="button"
                    @click="open = false"
                    class="p-2 -mr-2 text-gray-500 hover:text-gray-700"
                    aria-label="Filters sluiten"
                >
                    @svg('resources.images.icons.x-close')
                </button>
            </div>

            {{-- Mobile filter container (filters move here on mobile) --}}
            <div id="mobile-filters-container" class="flex-1 overflow-y-auto px-4 py-4"></div>

            {{-- Footer with results button --}}
            <div class="px-4 py-4 border-t border-gray-200 bg-white">
                <button
                    type="button"
                    @click="open = false"
                    class="w-full px-6 py-3.5 bg-red-600 text-white font-semibold rounded-full hover:bg-red-700 transition-colors"
                >
                    <span x-text="'Toon ' + totalResults + ' producten'"></span>
                </button>
            </div>
        </div>
    </template>

    {{-- On mobile, move the filters from the desktop sidebar into the mobile sheet --}}
    <template x-effect="
        const sidebar = document.querySelector('#filters-sidebar');
        const mobileTarget = document.querySelector('#mobile-filters-container');
        const desktopTarget = document.querySelector('#desktop-filters-target');
        if (!sidebar) return;
        if (!isDesktop && mobileTarget) {
            mobileTarget.appendChild(sidebar);
        } else if (isDesktop && desktopTarget && !desktopTarget.contains(sidebar)) {
            desktopTarget.appendChild(sidebar);
        }
    "></template>
</div>
