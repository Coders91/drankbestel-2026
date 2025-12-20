@props([
  'id' => null,
  'navigation' => false,
  'pagination' => false,
  'zoom' => false,
  'autoplay' => false,
  'thumbs' => false,
  'freeMode' => false,
  'slidesPerView' => 1,
  'slidesPerGroup' => 1,
  'spaceBetween' => 0,
  'loop' => false,
  'autoplayDelay' => 3000,
  'autoplayPauseOnMouseEnter' => true,
  'paginationType' => 'bullets',
  'navPrevClass' => null,
  'navNextClass' => null,
  'thumbsSlider' => null,
  'breakpoints' => [],
])

@php
  $sliderId = $id ?? 'slider-' . uniqid();

  $swiperOptions = [
    'slidesPerView' => $slidesPerView,
    'slidesPerGroup' => $slidesPerGroup,
    'spaceBetween' => $spaceBetween,
    'loop' => $loop,
  ];

  if ($navigation) {
    $swiperOptions['navigation'] = $navNextClass
      ? ['nextEl' => ".{$navNextClass}", 'prevEl' => ".{$navPrevClass}"]
      : true;
  }

  if ($pagination) {
    $swiperOptions['pagination'] = [
      'type' => $paginationType,
      'clickable' => true,
      'bulletSvg' => (string) get_svg('resources.images.icons.dot', 'size-2.5'),
    ];
  }

  if ($zoom) {
    $swiperOptions['zoom'] = [
      'maxRatio' => 3,
      'minRatio' => 1,
    ];
  }

  if ($autoplay) {
    $swiperOptions['autoplay'] = [
      'delay' => $autoplayDelay,
      'disableOnInteraction' => false,
      'pauseOnMouseEnter' => $autoplayPauseOnMouseEnter,
    ];
  }

  if ($freeMode) {
    $swiperOptions['freeMode'] = [
      'enabled' => true,
      'sticky' => false,
    ];
  }

  if ($thumbs && $thumbsSlider) {
    $swiperOptions['thumbsSlider'] = $thumbsSlider;
  }

  if (!empty($breakpoints)) {
    $swiperOptions['breakpoints'] = $breakpoints;
  }
@endphp

<div
  {{ $attributes->merge(['class' => 'slider-component']) }}
  x-data="Slider(@js($swiperOptions))"
>

  <div class="swiper" x-ref="swiper" id="{{ $sliderId }}">
    <div class="swiper-wrapper">
      {{ $slot }}
    </div>

    @if ($pagination)
      <div class="relative min-h-2.5 mt-8">
        <div class="flex justify-center gap-2.5" x-ref="pagination"></div>
      </div>
    @endif

    @if ($navigation && !$navNextClass)
      <button type="button" class="swiper-button-prev" x-ref="prev">
        <span class="sr-only">{{ __('Vorige', 'sage') }}</span>
      </button>
      <button type="button" class="swiper-button-next" x-ref="next">
        <span class="sr-only">{{ __('Volgende', 'sage') }}</span>
      </button>
    @endif
  </div>

  @isset($thumbs)
    <div class="slider-thumbs mt-4">
      {{ $thumbs }}
    </div>
  @endisset
</div>

@pushonce('scripts')
  <script src="{{ Vite::asset('resources/js/lib/swiper-bundle.min.js') }}"></script>
  <script>
    function Slider(options = {}) {
      return {
        swiper: null,
        options,
        init() {
          // Wait for Alpine x-for to render slides before initializing Swiper
          this.waitForSlidesAndInit();
        },
        waitForSlidesAndInit() {
          const wrapper = this.$refs.swiper?.querySelector('.swiper-wrapper');
          const slides = wrapper?.querySelectorAll('.swiper-slide');

          // If slides exist, initialize immediately
          if (slides && slides.length > 0) {
            this.initSwiper();
            return;
          }

          // Otherwise wait for x-for to render (check up to 10 times)
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
          const config = { ...this.options };

          if (config.navigation === true) {
            config.navigation = {
              nextEl: this.$refs.next,
              prevEl: this.$refs.prev,
            };
          }

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

          if (config.thumbsSlider) {
            config.thumbs = {
              swiper: window[config.thumbsSlider] || config.thumbsSlider,
            };
            delete config.thumbsSlider;
          }

          config.on = {
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

          // Wait for DOM to update then reinitialize
          this.$nextTick(() => {
            this.waitForSlidesAndInit();
          });
        },
        slideNext() {
          if (this.swiper) {
            this.swiper.slideNext();
          }
        },
        slidePrev() {
          if (this.swiper) {
            this.swiper.slidePrev();
          }
        },
        getRealIndex() {
          return this.swiper ? this.swiper.realIndex : 0;
        }
      }
    }
  </script>
@endpushonce
