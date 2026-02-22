<div
  {{ $attributes->merge(['class' => 'header-search relative']) }}
  x-data="{ open: @entangle('showDropdown'), focused: false }"
  @click.outside="if (!focused) open = false; dropdown = false"
>

    <button type="button" class="flex items-center gap-2 sm:hidden w-full bg-gray-50 pl-6 p-2 pb-3 text-gray-600 hover:text-red-600 transition-colors"
      wire:click="openMobileSearch" aria-label="{{ __('Zoeken', 'sage') }}">
      @svg('resources.images.icons.search-sm', 'size-6')
      Zoeken...
    </button>

  {{-- Desktop search form --}}
  <form wire:submit.prevent="goToSearch" class="relative hidden sm:flex lg:min-w-[640px]">
    <input
      type="search"
      id="header-search"
      name="header-search"
      class="w-full py-3 px-6 rounded-full h-10  lg:rounded-xl bg-gray-50 placeholder:text-gray-700 outline-0"
      placeholder="{{ __('Zoeken...', 'sage') }}"
      wire:model.live.debounce.300ms="query"
      @focus="focused = true; $wire.focusInput(); setTimeout(() => { backdrop = true }, 100);"
      @blur="focused = false; setTimeout(() => { if (!focused) open = false; backdrop = false }, 200);"
    />

    {{-- Clear button --}}
    @if($query)
      <span
        wire:click="$set('query', '')"
        class="absolute right-12 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600"
      >
        @svg('resources.images.icons.x', 'stroke-gray-700')
      </span>
    @endif

    <button type="submit" class="absolute right-3 -translate-y-1/2 top-1/2">
      @svg('resources.images.icons.search-sm', 'stroke-gray-700')
    </button>
  </form>

  {{-- Dropdown --}}
  <div
    x-show="open"
    x-transition
    class="absolute top-full left-0 right-0 z-50 bg-white rounded-b-xl shadow-lg max-h-96 overflow-y-auto"
  >
    {{-- Loading state with spinner --}}
    <div wire:loading wire:target="query" class="p-6">
      <div class="flex items-center justify-center gap-3">
        @svg('resources.images.icons.loader', 'animate-spin size-5 text-red-600')
        <span class="text-sm text-gray-500">{{ __('Zoeken...', 'sage') }}</span>
      </div>
    </div>

    {{-- Content --}}
    <div wire:loading.remove wire:target="query">
      {{-- Popular searches (shown when no query) --}}
      @if(!empty($popularSearches) && strlen($query) < 2)
        <div class="p-4">
          <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
            {{ __('Populaire zoekopdrachten', 'sage') }}
          </h3>
          <div class="flex flex-wrap gap-2">
            @foreach($popularSearches as $suggestion)
              <button
                type="button"
                x-on:mousedown.prevent
                wire:click="selectSuggestion('{{ $suggestion }}')"
                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-full text-sm transition-colors"
              >
                {{ $suggestion }}
              </button>
            @endforeach
          </div>
        </div>

      {{-- Search results --}}
      @elseif($searchResults && !$searchResults->isEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-4">

          {{-- Left column: Products --}}
          <div class="py-4 pl-4">
            @if($searchResults->hasProducts())
              <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                {{ __('Producten', 'sage') }}
              </h3>
              <div class="space-y-1">
                @foreach($searchResults->products as $product)
                  <x-search.product-item
                    :product="$product"
                    :query="$query"
                    :compact="true"
                    class="p-2 -mx-2 rounded-lg hover:bg-gray-50"
                  />
                @endforeach
              </div>
            @endif
          </div>

          {{-- Right column: Categories, Brands, Tags --}}
          <div class="py-4 pr-4 lg:border-l lg:border-gray-100 lg:pl-4">
            {{-- Categories --}}
            @if($searchResults->hasCategories())
              <div class="mb-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                  {{ __('Categorieën', 'sage') }}
                </h3>
                <div class="space-y-1">
                  @foreach($searchResults->categories as $category)
                    <x-search.category-item
                      :category="$category"
                      :query="$query"
                      :compact="true"
                      class="p-2 -mx-2 rounded-lg hover:bg-gray-50"
                    />
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Brands --}}
            @if($searchResults->hasBrands())
              <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                  {{ __('Merken', 'sage') }}
                </h3>
                <div class="space-y-1">
                  @foreach($searchResults->brands as $brand)
                    <x-search.brand-item
                      :brand="$brand"
                      :query="$query"
                      :compact="true"
                      class="p-2 -mx-2 rounded-lg hover:bg-gray-50"
                    />
                  @endforeach
                </div>
              </div>
            @endif
          </div>

          {{-- View all --}}
          @if($searchResults->totalCount() >= 5)
            <a
              href="{{ route('search', ['q' => $query]) }}"
              class="col-span-full block p-3 text-center text-sm text-red-600 transition-colors hover:bg-gray-50 border-t border-gray-100"
            >
              {{ __('Bekijk alle resultaten voor', 'sage') }} "{{ $query }}"
            </a>
          @endif
        </div>

      {{-- No results --}}
      @elseif($query && strlen($query) >= 2)
        <div class="p-4 text-center text-gray-500">
          {{ __('Geen resultaten gevonden voor', 'sage') }} "{{ $query }}"
        </div>
      @endif
    </div>
  </div>

  {{-- Mobile search overlay --}}
  @teleport('body')
  <div
    x-data="{ show: @entangle('showMobileOverlay') }"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] bg-white md:hidden"
    @keydown.escape.window="$wire.closeMobileSearch()"
  >
    {{-- Header with search input --}}
    <div class="sticky top-0 bg-white border-b border-gray-200 p-4 safe-area-inset-top">
      <form wire:submit.prevent="goToSearch" class="flex items-center gap-3">
        {{-- Back/close button --}}
        <button
          type="button"
          wire:click="closeMobileSearch"
          class="shrink-0 p-2 -m-2 text-gray-600"
          aria-label="{{ __('Sluiten', 'sage') }}"
        >
          @svg('resources.images.icons.arrow-left', 'size-6')
        </button>

        {{-- Search input --}}
        <div class="flex-1 relative">
          <input
            type="search"
            x-ref="mobileSearchInput"
            x-init="$watch('show', value => { if (value) setTimeout(() => $refs.mobileSearchInput.focus(), 100) })"
            class="w-full py-3 px-4 rounded-xl bg-gray-100 placeholder:text-gray-500 outline-0 text-base"
            placeholder="{{ __('Zoeken...', 'sage') }}"
            wire:model.live.debounce.300ms="query"
            autocomplete="off"
            autocorrect="off"
            autocapitalize="off"
            spellcheck="false"
          />
          @if($query)
            <button
              type="button"
              wire:click="$set('query', '')"
              class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-400"
            >
              @svg('resources.images.icons.x', 'size-5')
            </button>
          @endif
        </div>

        {{-- Search button --}}
        <button type="submit" class="shrink-0 p-2 -m-2 text-gray-600">
          @svg('resources.images.icons.search-sm', 'size-6')
        </button>
      </form>
    </div>

    {{-- Results area --}}
    <div class="overflow-y-auto h-[calc(100vh-80px)] overscroll-contain">
      {{-- Loading state with spinner --}}
      <div wire:loading wire:target="query" class="p-8">
        <div class="flex flex-col items-center justify-center gap-3">
          @svg('resources.images.icons.loader', 'animate-spin size-8 text-red-600')
          <span class="text-sm text-gray-500">{{ __('Zoeken...', 'sage') }}</span>
        </div>
      </div>

      {{-- Results --}}
      <div wire:loading.remove wire:target="query" class="p-4">
        @if($searchResults && !$searchResults->isEmpty())
          <div class="space-y-6">
            {{-- Products --}}
            @if($searchResults->hasProducts())
              <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                  {{ __('Producten', 'sage') }}
                </h3>
                <div class="space-y-3">
                  @foreach($searchResults->products as $product)
                    <x-search.product-item
                      :product="$product"
                      :query="$query"
                      :compact="true"
                      class="p-2 -mx-2 rounded-lg active:bg-gray-100"
                    />
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Categories --}}
            @if($searchResults->hasCategories())
              <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                  {{ __('Categorieën', 'sage') }}
                </h3>
                <div class="space-y-2">
                  @foreach($searchResults->categories as $category)
                    <x-search.category-item
                      :category="$category"
                      :query="$query"
                      :compact="true"
                    />
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Brands --}}
            @if($searchResults->hasBrands())
              <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                  {{ __('Merken', 'sage') }}
                </h3>
                <div class="space-y-2">
                  @foreach($searchResults->brands as $brand)
                    <x-search.brand-item
                      :brand="$brand"
                      :query="$query"
                      :compact="true"
                    />
                  @endforeach
                </div>
              </div>
            @endif

            {{-- View all link --}}
            @if($searchResults->totalCount() >= 5)
              <a
                href="{{ route('search', ['q' => $query]) }}"
                class="block py-4 text-center text-red-600 font-medium border-t border-gray-200"
              >
                {{ __('Bekijk alle resultaten', 'sage') }}
              </a>
            @endif
          </div>

        @elseif($query && strlen($query) >= 2)
          {{-- Empty state --}}
          <div class="py-12 text-center">
            @svg('resources.images.icons.search-lg', 'mx-auto size-12 text-gray-300')
            <p class="mt-4 text-gray-500">
              {{ __('Geen resultaten gevonden voor', 'sage') }} "{{ $query }}"
            </p>
          </div>

        @elseif(!empty($popularSearches))
          {{-- Popular searches --}}
          <div>
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">
              {{ __('Populaire zoekopdrachten', 'sage') }}
            </h3>
            <div class="flex flex-wrap gap-2">
              @foreach($popularSearches as $suggestion)
                <button
                  type="button"
                  x-on:mousedown.prevent
                  wire:click="selectSuggestion('{{ $suggestion }}')"
                  class="px-4 py-2 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 rounded-full text-sm transition-colors"
                >
                  {{ $suggestion }}
                </button>
              @endforeach
            </div>
          </div>

        @else
          {{-- Initial state - prompt to search --}}
          <div class="py-12 text-center text-gray-500">
            @svg('resources.images.icons.search-lg', 'mx-auto size-12 text-gray-300')
            <p class="mt-4">
              {{ __('Zoek naar producten, merken of categorieën', 'sage') }}
            </p>
          </div>
        @endif
      </div>
    </div>
  </div>
  @endteleport
</div>
