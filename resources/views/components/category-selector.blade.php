@props([
    'categories' => [],
    'threshold' => 6,
])

@php
    $count = count($categories);
    $useSlider = $count > $threshold;
@endphp

@if($count > 0)
    <section {{ $attributes->merge(['class' => 'py-10 mb-6 bg-gray-50']) }}>
      <div class="container">
        @if($useSlider)
            {{-- Slider mode for many categories --}}
            <x-slider
                class="category-selector-slider"
                :options="[
                    'slidesPerView' => 3,
                    'spaceBetween' => 12,
                    'freeMode' => true,
                    'navigation' => true,
                    'scrollbar' => [
                        'draggable' => true,
                        'hide' => false,
                    ],
                    'breakpoints' => [
                        480 => ['slidesPerView' => 4, 'spaceBetween' => 12],
                        640 => ['slidesPerView' => 5, 'spaceBetween' => 16],
                        768 => ['slidesPerView' => 6, 'spaceBetween' => 16],
                    ],
                ]"
            >
                @foreach($categories as $category)
                    <div class="swiper-slide h-auto">
                        <x-category-card :category="$category" />
                    </div>
                @endforeach
            </x-slider>
        @else
            {{-- Grid mode for few categories --}}
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 lg:gap-4">
                @foreach($categories as $category)
                    <x-category-card :category="$category" />
                @endforeach
            </div>
        @endif
      </div>
    </section>
@endif
