@php
  $currentVariation = collect($variations)->firstWhere('is_current', true);
  $currentUrl = $currentVariation['url'] ?? '';
@endphp

@if (count($variations) > 1)
  <div
    x-data="{
      open: false,
      currentUrl: '{{ $currentUrl }}',
      selectedUrl: '{{ $currentUrl }}',
      navigate() {
        if (this.selectedUrl && this.selectedUrl !== this.currentUrl) {
          window.location.href = this.selectedUrl;
        } else {
          this.open = false;
        }
      }
    }"
    {{ $attributes->merge(['class' => '']) }}
  >
    <p class="text-sm mb-2 text-gray-900 font-semibold">
      {{ __('Flesformaat', 'sage') }}
    </p>

    {{-- Desktop: Pills (≤3 variations) --}}
    @unless ($showSelect)
      <div class="hidden md:flex flex-wrap gap-2.5">
        @foreach ($variations as $variation)
          @if ($variation['is_current'])
            <span class="inline-flex items-center gap-2 rounded-lg px-[15px] py-2 border border-red-600 text-red-600 font-semibold">
              <span class="text-sm">{{ $variation['contents'] ?: __('Standaard', 'sage') }}</span>
            </span>
          @elseif($variation['is_in_stock'])
            <a
              href="{{ $variation['url'] }}"
              class="inline-flex items-center gap-2 px-[15px] py-2 rounded-lg border border-gray-300 bg-white hover:border-red-600 hover:bg-red-50 transition-colors"
            >
              <span class="text-sm">{{ $variation['contents'] ?: __('Standaard', 'sage') }}</span>
            </a>
          @endif
        @endforeach
      </div>
    @endunless

    {{-- Desktop: Select (>3 variations) --}}
    @if ($showSelect)
      <div class="hidden md:block">
        <x-forms.select
          id="size-selector"
          name="size-selector"
          @change="window.location.href = $event.target.value"
        >
          @foreach ($variations as $variation)
            <option
              value="{{ $variation['url'] }}"
              {{ $variation['is_current'] ? 'selected' : '' }}
              {{ !$variation['is_in_stock'] ? 'disabled' : '' }}
            >
              {!! $variation['contents'] ?: __('Standaard', 'sage') !!} - {!! strip_tags($variation['price_formatted']) !!}
              @unless ($variation['is_in_stock'])
                ({{ __('Uitverkocht', 'sage') }})
              @endunless
            </option>
          @endforeach
        </x-forms.select>
      </div>
    @endif

    {{-- Mobile: Button to open bottom sheet --}}
    <div class="md:hidden">
      <button
        type="button"
        @click="open = true"
        class="w-full flex items-center justify-between px-4 py-3 border border-gray-300 rounded-lg bg-white"
      >
        <span class="flex items-center gap-2">
          <span class="font-medium">
            {{ $currentVariation['contents'] ?: __('Standaard', 'sage') }}
          </span>
          <span class="text-gray-500">{!! $currentVariation['price_formatted'] !!}</span>
        </span>
        @svg('resources.images.icons.chevron-selector-vertical', 'size-5 text-gray-400')
      </button>
    </div>

    {{-- Mobile: Bottom Sheet --}}
    <template x-teleport="body">
      <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 md:hidden"
        @keydown.escape.window="open = false"
      >
        {{-- Backdrop --}}
        <div
          x-show="open"
          x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          class="fixed inset-0 bg-black/50"
          @click="open = false"
        ></div>

        {{-- Bottom Sheet --}}
        <div
          x-show="open"
          x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="translate-y-full"
          x-transition:enter-end="translate-y-0"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="translate-y-0"
          x-transition:leave-end="translate-y-full"
          class="fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-xl max-h-[80vh] overflow-hidden flex flex-col"
        >
          {{-- Header --}}
          <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">{{ __('Kies formaat', 'sage') }}</h3>
            <button
              type="button"
              @click="open = false"
              class="p-2 -mr-2 text-gray-400 hover:text-gray-600"
            >
              @svg('resources.images.icons.x', 'size-5')
            </button>
          </div>

          {{-- Options --}}
          <div class="flex-1 overflow-y-auto px-4 py-4">
            <div class="space-y-2">
              @foreach ($variations as $variation)
                <label
                  class="flex items-center gap-3 p-4 rounded-lg border cursor-pointer transition-colors {{ !$variation['is_in_stock'] ? 'opacity-50 cursor-not-allowed' : '' }}"
                  :class="selectedUrl === '{{ $variation['url'] }}' ? 'border-red-600 bg-red-50' : 'border-gray-200 hover:border-gray-300'"
                  @unless ($variation['is_in_stock'])
                    @click.prevent
                  @endunless
                >
                  <input
                    type="radio"
                    name="size_variation"
                    value="{{ $variation['url'] }}"
                    {{ !$variation['is_in_stock'] ? 'disabled' : '' }}
                    x-model="selectedUrl"
                    class="flex-shrink-0 size-5 text-red-600 border-gray-300 focus:ring-red-500"
                  >
                  <div class="flex-1 flex items-center justify-between">
                    <span class="font-medium">
                      {{ $variation['contents'] ?: __('Standaard', 'sage') }}
                    </span>
                    <div class="text-right">
                      @if ($variation['is_on_sale'])
                        <span class="text-red-600 font-semibold">{!! $variation['price_formatted'] !!}</span>
                        <span class="text-sm text-gray-400 line-through ml-1">{!! $variation['regular_price_formatted'] !!}</span>
                      @else
                        <span class="font-semibold">{!! $variation['price_formatted'] !!}</span>
                      @endif
                      @unless ($variation['is_in_stock'])
                        <span class="block text-xs text-red-500">{{ __('Uitverkocht', 'sage') }}</span>
                      @endunless
                    </div>
                  </div>
                </label>
              @endforeach
            </div>
          </div>

          {{-- Footer with confirm button --}}
          <div class="px-4 py-4 border-t border-gray-200 bg-gray-50">
            <button
              type="button"
              @click="navigate()"
              class="w-full py-3 px-4 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors"
            >
              <span x-show="selectedUrl !== currentUrl">{{ __('Bekijk product', 'sage') }}</span>
              <span x-show="selectedUrl === currentUrl">{{ __('Sluiten', 'sage') }}</span>
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
@endif
