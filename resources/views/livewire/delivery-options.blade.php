<div
  wire:ignore
  x-data="deliveryOptions({
    postalCode: @js($postalCode),
    houseNumber: @js($houseNumber),
    houseNumberSuffix: @js($houseNumberSuffix),
    settings: @js($settings),
    placeholderOptions: @js($placeholderOptions),
    initialSelection: @js($initialSelection),
  })"
  x-init="init()"
  @fetch-delivery-options.window="fetchOptions()"
>
  <div x-show="options.length > 0">
    <div class="max-sm:hidden flex flex-wrap items-center gap-4 w-full justify-between mt-4 md:mt-0 mb-4">
      Kies een bezorgdatum
      <div class="flex items-center gap-2">
        <button type="button" class="swiper-button-prev-custom flex items-center justify-center size-10 rounded-full border border-gray-300 hover:bg-gray-100 transition-colors">
          @svg('resources.images.icons.chevron-left')
        </button>
        <button type="button" class="swiper-button-next-custom flex items-center justify-center size-10 rounded-full border border-gray-300 hover:bg-gray-100 transition-colors">
          @svg('resources.images.icons.chevron-right')
        </button>
      </div>
    </div>

    <div class="swiper" x-ref="swiperContainer">
      <div class="swiper-wrapper" x-ref="swiperWrapper">
        {{-- Slides rendered via JavaScript --}}
      </div>
    </div>

    <p class="mt-4 text-sm text-gray-900">Bij aflevering vindt leeftijdscontrole plaats.</p>
  </div>

  {{-- Hidden input for form submission --}}
  <input
    x-show="selection"
    type="hidden"
    name="_myparcel_delivery_options"
    :value="JSON.stringify(selection)"
  >
</div>

<script>
function deliveryOptions(config) {
  return {
    // State
    options: config.placeholderOptions || [],
    selectedIndex: 0,
    selection: config.initialSelection || null,
    isLoading: false,
    error: null,
    swiper: null,

    // Config
    postalCode: config.postalCode || '',
    houseNumber: config.houseNumber || '',
    houseNumberSuffix: config.houseNumberSuffix || '',
    settings: config.settings || {},

    init() {
      // Render initial slides and init swiper
      this.renderSlides();
      this.initSwiper();

      // Set initial selection
      if (this.selection?.date) {
        this.restoreSelectionByDate(this.selection.date);
      } else if (this.options.length > 0) {
        this.selectByIndex(0);
      }

      // Initial fetch if we have address
      if (this.postalCode && this.houseNumber) {
        this.fetchOptions();
      }
    },

    renderSlides(reinit = false) {
      const wrapper = this.$refs.swiperWrapper;
      if (!wrapper) return;

      const loading = `Laden...`;

      // If just updating loading state and Swiper exists, update slide content only
      if (this.swiper && !reinit) {
        const slides = wrapper.querySelectorAll('.swiper-slide');
        slides.forEach((slide, index) => {
          if (this.options[index]) {
            const timeSlot = slide.querySelector('.mt-1.text-sm');
            if (timeSlot) {
              timeSlot.innerHTML = this.isLoading ? loading : (this.options[index].time_string || '');
            }
          }
        });
        return;
      }

      // Full render for init or when options change
      wrapper.innerHTML = this.options.map((option, index) => `
        <button
          type="button"
          data-index="${index}"
          class="swiper-slide rounded-lg border-2 p-4 text-left transition-all cursor-pointer ${this.selectedIndex === index ? 'border-gray-900 bg-white' : 'bg-gray-100 hover:bg-gray-200 border-transparent'}"
        >
          <div class="font-semibold capitalize">${option.display_date}</div>
          <div class="mt-1 text-gray-800">${option.date_string}</div>
          <div class="mt-1 text-sm font-medium text-gray-800 flex items-center gap-2">
            ${this.isLoading ? loading : (option.time_string || '')}
          </div>
        </button>
      `).join('');

      // Add click handlers
      wrapper.querySelectorAll('.swiper-slide').forEach(slide => {
        slide.addEventListener('click', () => {
          const index = parseInt(slide.dataset.index, 10);
          this.selectByIndex(index);
        });
      });
    },

    updateSlideStyles() {
      const wrapper = this.$refs.swiperWrapper;
      if (!wrapper) return;

      wrapper.querySelectorAll('.swiper-slide').forEach(slide => {
        const index = parseInt(slide.dataset.index, 10);
        if (index === this.selectedIndex) {
          slide.classList.remove('bg-gray-100', 'hover:bg-gray-200', 'border-transparent');
          slide.classList.add('border-gray-900', 'bg-white');
        } else {
          slide.classList.remove('border-gray-900', 'bg-white');
          slide.classList.add('bg-gray-100', 'hover:bg-gray-200', 'border-transparent');
        }
      });
    },

    initSwiper() {
      if (this.swiper) {
        this.swiper.destroy(true, true);
      }

      const container = this.$refs.swiperContainer;
      if (!container) return;

      this.swiper = new Swiper(container, {
        slidesPerView: 1.5,
        spaceBetween: 16,
        loop: this.options.length > 4,
        slideToClickedSlide: true,
        watchSlidesProgress: true,
        navigation: {
          nextEl: '.swiper-button-next-custom',
          prevEl: '.swiper-button-prev-custom',
        },
        breakpoints: {
          575: {
            slidesPerView: 3,
          },
          768: {
            slidesPerView: 4,
          }
        },
        on: {
          slideChangeTransitionStart: (swiper) => {
            this.selectByIndex(swiper.realIndex);
          },
        },
      });
    },

    async fetchOptions() {
      const postalCode = this.$wire.postalCode || this.postalCode;
      const houseNumber = this.$wire.houseNumber || this.houseNumber;
      const suffix = this.$wire.houseNumberSuffix || this.houseNumberSuffix || '';

      if (!postalCode || !houseNumber) {
        return;
      }

      this.isLoading = true;
      this.error = null;
      this.renderSlides(); // Just update time slots to show spinners (doesn't reinit Swiper)

      const preservedDate = this.selection?.date || null;

      try {
        const params = new URLSearchParams({
          cc: 'NL',
          platform: 'myparcel',
          postal_code: postalCode,
          number: houseNumber + (suffix ? ' ' + suffix : ''),
          deliverydays_window: this.settings.delivery_days_window || 10,
          monday_delivery: this.settings.allow_monday_delivery ? '1' : '0',
          saturday_delivery: this.settings.allow_saturday_delivery ? '1' : '0',
          evening_delivery: this.settings.allow_evening_delivery ? '1' : '0',
          cutoff_time: this.settings.cutoff_time || '17:00',
          carrier: this.settings.carrier || 'postnl',
          excluded_delivery_types: this.getExcludedDeliveryTypes(),
        });

        const response = await fetch(`https://api.myparcel.nl/delivery_options?${params}`);

        if (!response.ok) {
          throw new Error('API request failed');
        }

        const data = await response.json();
        const deliveryData = data?.data?.delivery || data?.delivery || [];

        if (!deliveryData.length) {
          this.error = 'Geen bezorgopties gevonden voor dit adres.';
          this.isLoading = false;
          this.renderSlides(); // Update to remove spinners
          return;
        }

        this.options = this.formatDeliveryOptions(deliveryData);
        this.isLoading = false;

        // Re-render with new options and reinit swiper
        this.renderSlides(true); // true = full reinit
        this.initSwiper();

        // Restore or select first
        if (preservedDate && !this.restoreSelectionByDate(preservedDate)) {
          this.selectByIndex(0);
        } else if (!preservedDate && this.options.length > 0) {
          this.selectByIndex(0);
        }

      } catch (e) {
        console.error('Delivery options fetch error:', e);
        this.error = 'Kon bezorgtijden niet ophalen.';
        this.isLoading = false;
        this.renderSlides(); // Update to remove spinners
      }
    },

    formatDeliveryOptions(options) {
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      return options
        .map(option => {
          const date = new Date(option.date);
          date.setHours(0, 0, 0, 0);

          if (date < today) return null;

          const daysDiff = Math.floor((date - today) / (1000 * 60 * 60 * 24));
          const dayName = date.toLocaleDateString('nl-NL', { weekday: 'long' });

          let displayDate;
          if (daysDiff === 0) {
            displayDate = 'vandaag';
          } else if (daysDiff === 1) {
            displayDate = `morgen (${dayName})`;
          } else if (daysDiff === 2) {
            displayDate = `overmorgen (${dayName})`;
          } else {
            displayDate = dayName;
          }

          // Find standard delivery time slot (type 2)
          const timeSlot = (option.time || []).find(slot => (slot.type || 2) === 2);
          if (!timeSlot) return null;

          return {
            date: option.date,
            display_date: displayDate,
            date_string: date.toLocaleDateString('nl-NL', { day: 'numeric', month: 'long' }),
            time_string: `${timeSlot.start.slice(0, -3)} - ${timeSlot.end.slice(0, -3)}`,
            start: timeSlot.start,
            end: timeSlot.end,
            is_placeholder: false,
          };
        })
        .filter(Boolean);
    },

    getExcludedDeliveryTypes() {
      const excluded = [];
      if (!this.settings.allow_morning_delivery) excluded.push('1');
      if (!this.settings.allow_evening_delivery) excluded.push('3');
      if (!this.settings.allow_pickup_locations) excluded.push('4');
      return excluded.join(';');
    },

    selectByIndex(index) {
      if (index < 0 || index >= this.options.length) return;

      this.selectedIndex = index;
      const option = this.options[index];

      this.selection = {
        isPickup: false,
        carrier: this.settings.carrier || 'postnl',
        package_type: 'package',
        deliveryType: 'standard',
        shipmentOptions: { same_day_delivery: false },
        date: option.date,
        start: option.start,
        end: option.end,
      };

      // Update visual selection
      this.updateSlideStyles();

      // Sync to Livewire
      this.$wire.updateSelection(this.selection);
    },

    restoreSelectionByDate(dateToFind) {
      const search = dateToFind.substring(0, 10);

      for (let i = 0; i < this.options.length; i++) {
        if (this.options[i].date.substring(0, 10) === search) {
          this.selectedIndex = i;
          this.updateSlideStyles();
          return true;
        }
      }
      return false;
    },
  };
}
</script>
