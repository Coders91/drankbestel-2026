@props([
  'id' => null,
  'options' => [],
])

@php
  $sliderId = $id ?? 'slider-' . uniqid();

  // Check options for conditional UI elements
  $hasNavigation = !empty($options['navigation']);
  $hasPagination = !empty($options['pagination']);
  $hasScrollbar = !empty($options['scrollbar']);
  $hasCustomNavEl = is_array($options['navigation'] ?? null) && isset($options['navigation']['nextEl']);
@endphp

<div
  {{ $attributes->merge(['class' => 'slider-component']) }}
  x-data="Slider(@js($options))"
>
  <div class="swiper" x-ref="swiper" id="{{ $sliderId }}">
    <div class="swiper-wrapper">
      {{ $slot }}
    </div>

    @if ($hasPagination)
      <div class="relative min-h-2.5 mt-8">
        <div class="flex justify-center gap-2.5" x-ref="pagination"></div>
      </div>
    @endif

    @if ($hasScrollbar)
      <div class="swiper-scrollbar mt-4" x-ref="scrollbar"></div>
    @endif

    @if ($hasNavigation && !$hasCustomNavEl)
      <button type="button"
              class="group absolute z-10 right-auto left-0 -translate-y-1/2 top-1/2 size-14 rounded-full bg-white flex justify-center items-center shadow-md border border-gray-200"
              disabled
              x-ref="prev"
      >
        @svg('resources.images.icons.chevron-left', 'size-7 group-hover:stroke-red-600')
        <span class="sr-only">{{ __('Vorige', 'sage') }}</span>
      </button>
      <button type="button"
              class="group absolute z-10 left-auto right-0 -translate-y-1/2 top-1/2 size-14 rounded-full bg-white flex justify-center items-center shadow-md border border-gray-200"
              x-ref="next"
      >
        @svg('resources.images.icons.chevron-right', 'size-7 group-hover:stroke-red-600')
        <span class="sr-only">{{ __('Volgende', 'sage') }}</span>
      </button>
    @endif
  </div>

  @isset($thumbs)
    <div class="slider-thumbs mt-4">
      {{ $thumbs }}
    </div>
  @endisset

  @pushonce('scripts')
    <script>
      function Slider(options = {}) {
        return {
          swiper: null,
          options,
          init() {
            this.initSwiper();
          },
          waitForSlidesAndInit() {
            const wrapper = this.$refs.swiper?.querySelector('.swiper-wrapper');
            const slides = wrapper?.querySelectorAll('.swiper-slide');

            if (slides && slides.length > 0) {
              this.initSwiper();
              return;
            }

            let attempts = 0;
            const checkInterval = setInterval(() => {
              attempts++;
              const newSlides = wrapper?.querySelectorAll('.swiper-slide');

              if ((newSlides && newSlides.length > 0) || attempts >= 10) {
                clearInterval(checkInterval);
                if (newSlides && newSlides.length > 0) {
                  this.initSwiper();
                }
              }
            }, 50);
          },
          initSwiper() {
            console.log(this.options);
            const config = { ...this.options };

            // Bind navigation to refs if set to true
            if (config.navigation === true) {
              config.navigation = {
                nextEl: this.$refs.next,
                prevEl: this.$refs.prev,
              };
            }

            // Bind pagination to ref and handle custom bullet rendering
            if (config.pagination) {
              const bulletSvg = config.pagination.bulletSvg;
              config.pagination = {
                ...config.pagination,
                el: this.$refs.pagination,
              };
              delete config.pagination.bulletSvg;

              if (bulletSvg) {
                config.pagination.renderBullet = (index, className) => {
                  return `<span class="${className}">${bulletSvg}</span>`;
                };
              }
            }

            // Bind scrollbar to ref
            if (config.scrollbar && this.$refs.scrollbar) {
              config.scrollbar = {
                ...config.scrollbar,
                el: this.$refs.scrollbar,
              };
            }

            // Handle thumbs slider reference
            if (config.thumbsSlider) {
              config.thumbs = {
                swiper: window[config.thumbsSlider] || config.thumbsSlider,
              };
              delete config.thumbsSlider;
            }

            // Add slide change event
            config.on = {
              ...config.on,
              slideChangeTransitionStart: (swiper) => {
                this.$dispatch('slide-change', { index: swiper.realIndex });
              },
            };

            this.swiper = new Swiper(this.$refs.swiper, config);
          },
          refresh() {
            if (this.swiper) {
              this.swiper.destroy(true, true);
              this.swiper = null;
            }
            this.$nextTick(() => this.waitForSlidesAndInit());
          },
          slideNext() {
            this.swiper?.slideNext();
          },
          slidePrev() {
            this.swiper?.slidePrev();
          },
          slideTo(index) {
            this.swiper?.slideTo(index);
          },
          getRealIndex() {
            return this.swiper?.realIndex ?? 0;
          }
        }
      }
    </script>
  @endpushonce
</div>
