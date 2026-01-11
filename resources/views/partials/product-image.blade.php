{{-- Product Gallery --}}
<div class="col-start-1 lg:sticky lg:top-8 md:row-span-full flex flex-col-reverse lg:flex-row">
  {{-- Thumbnail Navigation --}}
  @if ($product->hasMultipleImages())
    <div
      class="flex lg:flex-col gap-2 lg:gap-3 lg:w-24 shrink-0 overflow-x-auto lg:overflow-y-auto lg:max-h-[500px]">
      @foreach ($product->allImages as $index => $image_id)
        <button
          type="button"
          @click="activeSlide = {{ $index }}; window.dispatchEvent(new CustomEvent('product-gallery-slide-to', { detail: { index: {{ $index }} } }))"
          class="shrink-0 w-20 h-20 lg:w-full lg:h-24 p-2 rounded-lg overflow-hidden border-2 transition-colors"
          :class="activeSlide === {{ $index }} ? 'border-red-600' : 'border-gray-200 hover:border-gray-400'"
        >
          <x-image
            :id="$image_id"
            size="thumbnail"
            class="w-full h-full object-contain"
          />
        </button>
      @endforeach
    </div>
  @endif

  {{-- Main Image Slider --}}
  <div class="flex-1 relative">
    {{-- Sale Badge --}}
    @if ($product->is_on_sale && $product->discountPercentage > 0)
      <div class="absolute top-4 left-4 z-10 bg-red-600 text-white font-bold text-sm px-3 py-1 rounded-full">
        -{{ $product->discountPercentage }}%
      </div>
    @endif

    {{-- Wishlist Button --}}
    <button
      type="button"
      x-on:click.prevent="toggleFavorite({{ $product->id }})"
      class="absolute top-4 right-4 z-10 flex justify-center items-center size-12 rounded-full bg-white/90 hover:bg-white shadow-md transition-all hover:scale-110"
      x-bind:title="isFavorite({{ $product->id }}) ? '{{ __('Verwijderen uit favorieten', 'sage') }}' : '{{ __('Toevoegen aan favorieten', 'sage') }}'"
    >
              <span
                x-bind:class="isFavorite({{ $product->id }}) ? '*:text-red-600 *:fill-red-600' : '*:text-gray-600 *:hover:text-red-600'">
                @svg('resources.images.icons.heart', 'size-6 transition-colors')
              </span>
    </button>

    @if ($product->hasMultipleImages())
      <x-slider
        id="product-gallery"
        class="product-gallery-slider sticky top-0 rounded-2xl overflow-hidden aspect-square"
        :options="[
                  'slidesPerView' => 1,
                  'spaceBetween' => 0,
                  'navigation' => true,
                ]"
        x-on:slide-change="activeSlide = $event.detail.index"
        x-on:product-gallery-slide-to.window="slideTo($event.detail.index)"
      >
        @foreach ($product->allImages as $image_id)
          <div class="swiper-slide">
            <div class="h-full w-full flex items-center justify-center p-8">
              <x-image
                :id="$image_id"
                size="full"
                class="max-w-full max-h-full object-contain"
                :fetchpriorityHigh="$loop->first"
              />
            </div>
          </div>
        @endforeach
      </x-slider>
    @else
      <div class="sticky top-0 rounded-2xl overflow-hidden">
        <div class="aspect-square flex items-center justify-center p-8">
          @if ($product->imageId)
            <x-image
              :id="$product->imageId"
              size="full"
              class="max-w-full max-h-full object-contain"
              :fetchpriorityHigh="true"
            />
          @else
            <div class="text-gray-400">
              @svg('resources.images.icons.image', 'size-24')
            </div>
          @endif
        </div>
      </div>
    @endif

    {{-- Image Counter (mobile) --}}
    @if ($product->hasMultipleImages())
      <div
        class="lg:hidden absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/60 text-white text-sm px-3 py-1 rounded-full">
        <span x-text="activeSlide + 1"></span> / {{ $product->imageCount() }}
      </div>
    @endif
  </div>
</div>
