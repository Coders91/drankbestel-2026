{{--
The template for displaying product content in the single-product.php template

This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.

HOWEVER, on occasion WooCommerce will need to update template files and you
(the theme developer) will need to copy the new files to your theme to
maintain compatibility. We try to do this as little as possible, but it does
happen. When this occurs the version of the template file will be bumped and
the readme will list any important changes.

@see     https://docs.woocommerce.com/document/template-structure/
@package WooCommerce\Templates
@version 3.6.0
--}}

<div id="product-{{ $product->id }}" {{ wc_product_class('', $product) }}
x-data="{
    activeSlide: 0,
    imageCount: {{ $product->imageCount() }},
  }"
     x-init="trackProduct({{ $product->id }})"
>
  {{-- Main Product Section --}}
  <section class="pt-6 pb-12 lg:pb-20">
    <div class="container">
      <div class="grid md:grid-cols-2 xl:grid-cols-[680px_1fr] grid-rows-[repeat(7,auto)] *:self-start gap-x-8">

        @include('partials.product-image')

        <div class="max-md:row-start-1 md:col-start-2">
          {{-- Title --}}
          <h1 class="display-1 mb-2">
            {{ $product->name }}
            @if ($product->contents)
              <span class="block mt-2 text-lg text-gray-600 font-body font-normal">{{ $product->contents }}</span>
            @endif
          </h1>

          {{-- Rating --}}
          @if ($product->hasReviews())
            <div class="flex items-center gap-2 mt-2 mb-2">
              <x-star-rating :rating="$product->rating" size="md" />
              <a href="#reviews"
                 class="text-sm text-gray-500 hover:text-red-600 transition-colors"
                 @click.prevent="$dispatch('accordion-open', { id: 'reviews' })"
              >
                {{ $product->reviewCount }} {{ $product->reviewCount === 1 ? __('review', 'sage') : __('reviews', 'sage') }}
              </a>
            </div>
          @endif
        </div>

        {{-- Size Selector --}}
        @if ($product->hasSizeVariations())
          <x-size-selector class="md:col-start-2 mt-4" :size-variations="$product->sizeVariations" />
        @endif

        {{-- Price --}}
        <div class="md:col-start-2 mt-7 mb-6 pb-6 border-b border-gray-200">
          @if ($product->is_on_sale)
            <div class="flex flex-col items-baseline gap-3">
              <span class="text-xl text-gray-400 line-through">
                {{ $product->price->regular->formatted() }}
              </span>
              <span class="display-2 text-red-600">
                {{ $product->price->sale->formatted() }}
              </span>
              @if ($product->discountPercentage > 0)
                <span class="bg-red-100 text-red-600 text-sm font-semibold px-2 py-1 rounded">
                  {{ __('Bespaar', 'sage') }} {{ $product->discountPercentage }}%
                </span>
              @endif
            </div>
          @else
            <span class="display-2">
              {{ $product->price->regular->formatted() }}
            </span>
          @endif

          {{-- Stock Status --}}
            @if ($product->is_in_stock)
              <div class="mt-3 flex items-center gap-2">
                  @svg('resources.images.icons.check-circle', 'size-5 text-green-600')
                  <span class="text-sm font-medium text-green-600">{{ __('Op voorraad', 'sage') }}</span>
              </div>
            @endif
        </div>

        {{-- Short Description --}}
        @if ($product->shortDescription)
          <div class="md:col-start-2 text-gray-600 mb-6">
            {!! $product->shortDescription !!}
          </div>
        @endif

        {{-- Add to Cart Form --}}
        <div class="md:col-start-2">
          @if($product->is_in_stock)
            <livewire:add-to-cart :productId="$product->id" :isSingleProduct="true" />
          @else
            <h3 class="display-6">Uitverkocht</h3>
          @endif
          {{-- Trust Badges --}}
          <x-usps :usps="$product->usps" variant="horizontal" :columns="2" class="mt-6 mb-8"/>
          @include('partials.product-details')
        </div>
      </div>
    </div>
  </section>

  {{-- Upsell Products --}}
  @if ($product->hasUpsells())
    <x-section theme="lightgray" title="{{ __('Gerelateerde producten', 'sage') }}">
      <x-slider
        :options="[
          'slidesPerView' => 2,
          'spaceBetween' => 16,
          'navigation' => true,
          'breakpoints' => [
            768 => [
              'slidesPerView' => 3,
            ],
            1024 => [
              'slidesPerView' => 4,
            ],
          ],
        ]"
      >
        @foreach ($product->upsellProducts as $upsellProduct)
          <div class="swiper-slide">
            <x-woocommerce.product :product="$upsellProduct"/>
          </div>
        @endforeach
      </x-slider>
    </x-section>
  @endif

</div>
