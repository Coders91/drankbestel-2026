@props([
  'usps' => [],
])

<div
  {{ $attributes->merge(['class' => 'usp-bar leading-0 lg:h-8']) }}
  x-data="{ mounted: false }"
  x-init="setTimeout(() => mounted = true, 50)"
  :class="{ 'invisible': !mounted }"
  style="transition: opacity 0.3s ease-in-out;"
  :style="mounted ? 'opacity: 1' : 'opacity: 0'"
>
  <div class="container mx-auto">
    <x-slider
      class="usp-slider"
      :options="[
        'slidesPerView' => 1,
        'direction' => 'vertical',
        'speed' => 800,
        'loop' => true,
        'effect' => 'fade',
        'centeredSlides' => true,
        'fadeEffect' => [
          'crossFade' => true,
        ],
        'autoplay' => [
          'delay' => 5000,
          'reverseDirection' => true,
          'disableOnInteraction' => false,
        ],
      ]"
    >
      @foreach ($usps as $usp)
        <div class="swiper-slide">
          <div class="flex items-center gap-2">
            <span>{{ $usp }}</span>
          </div>
          </div>
      @endforeach
    </x-slider>
  </div>
</div>
