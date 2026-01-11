<div
  x-init="
    @if (!$this->hasProducts)
      if (getRecentlyViewed({{ $excludeId ?? 'null' }}).length > 0) {
        $wire.loadProducts(getRecentlyViewed({{ $excludeId ?? 'null' }}));
      }
    @endif
  "
>
  @if($this->hasProducts)
    <x-section theme="gray" title="Recent bekeken">
      <x-slider
        :options="[
           'slidesPerView' => 4,
           'spaceBetween' => 16,
           'navigation' => true,
        ],
      ">
          @foreach ($this->products as $product)
          <div class="swiper-slide">
            <x-woocommerce.product :product="$product" />
          </div>
          @endforeach
      </x-slider>
    </x-section>
  @endif
</div>
