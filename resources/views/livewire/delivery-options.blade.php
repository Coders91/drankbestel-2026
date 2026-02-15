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
  @fetch-delivery-options.window="fetchOptions()"
>
  <div x-show="options.length > 0">
    <div class="flex flex-wrap items-center gap-4 w-full justify-between mt-4 md:mt-0 mb-4">
      <span class="font-semibold text-sm" x-text="selectedLabel"></span>
      <div class="max-sm:hidden flex items-center gap-2">
        <button
          type="button"
          class="swiper-button-prev-custom size-10 rounded-full border border-gray-300 transition-colors disabled:opacity-50 disabled:bg-gray-50 disabled:border-gray-100"
        >
          @svg('resources.images.icons.chevron-left', 'size-5 m-auto')
        </button>
        <button
          type="button"
          class="swiper-button-next-custom size-10 rounded-full border border-gray-300 transition-colors disabled:opacity-50 disabled:bg-gray-50 disabled:border-gray-100"
        >
          @svg('resources.images.icons.chevron-right', 'size-5 m-auto')
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
    options: config.placeholderOptions || [],
    selectedIndex: 0,
    selection: config.initialSelection || null,
    isLoading: false,
    error: null,
    swiper: null,

    postalCode: config.postalCode || '',
    houseNumber: config.houseNumber || '',
    houseNumberSuffix: config.houseNumberSuffix || '',
    settings: config.settings || {},

    get selectedLabel() {
      if (this.selectedIndex >= 0 && this.options[this.selectedIndex]) {
        const option = this.options[this.selectedIndex];
        return `Bezorgdatum: ${option.display_date} ${option.date_string}`;
      }
      return 'Kies een bezorgdatum';
    },

    init() {
      this.renderSlides();
      this.initSwiper();

      if (this.postalCode && this.houseNumber) {
        this.prePositionToSelection();
        this.fetchOptions();
      } else if (this.selection?.date) {
        this.restoreSelection();
      } else if (this.options.length > 0) {
        this.selectByIndex(0);
      }
    },

    prePositionToSelection() {
      if (!this.selection?.date) return;

      const index = this.findIndexByDate(this.selection.date);
      if (index < 0) return;

      this.selectedIndex = index;
      this.updateSlideStyles();
      this.swiper?.slideTo(index, 0);
    },

    renderSlides(reinit = false) {
      const wrapper = this.$refs.swiperWrapper;
      if (!wrapper) return;

      if (this.swiper && !reinit) {
        wrapper.querySelectorAll('.swiper-slide').forEach((slide, i) => {
          const timeSlot = slide.querySelector('[data-time]');
          if (timeSlot && this.options[i]) {
            timeSlot.innerHTML = this.isLoading ? 'Laden...' : (this.options[i].time_string || '');
          }
        });
        return;
      }

      wrapper.innerHTML = this.options.map((option, index) => `
        <button
          type="button"
          data-index="${index}"
          class="swiper-slide rounded-lg border-2 p-4 text-left transition-all cursor-pointer"
        >
          <div class="font-semibold capitalize">${option.display_date}</div>
          <div class="mt-1 text-gray-800">${option.date_string}</div>
          <div data-time class="mt-1 text-sm font-medium text-gray-800 flex items-center gap-2">
            ${this.isLoading ? 'Laden...' : (option.time_string || '')}
          </div>
        </button>
      `).join('');

      wrapper.querySelectorAll('.swiper-slide').forEach(slide => {
        slide.addEventListener('click', () => {
          this.selectByIndex(parseInt(slide.dataset.index, 10));
        });
      });
    },

    updateSlideStyles() {
      const wrapper = this.$refs.swiperWrapper;
      if (!wrapper) return;

      wrapper.querySelectorAll('.swiper-slide').forEach(slide => {
        const isSelected = parseInt(slide.dataset.index, 10) === this.selectedIndex;
        slide.classList.toggle('border-red-600', isSelected);
        slide.classList.toggle('bg-white', isSelected);
        slide.classList.toggle('bg-gray-100', !isSelected);
        slide.classList.toggle('hover:bg-gray-200', !isSelected);
        slide.classList.toggle('border-transparent', !isSelected);
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
        freeMode: true,
        navigation: {
          nextEl: '.swiper-button-next-custom',
          prevEl: '.swiper-button-prev-custom',
        },
        breakpoints: {
          575: { slidesPerView: 3, slidesPerGroup: 3 },
          768: { slidesPerView: 4, slidesPerGroup: 4 },
        },
      });
    },

    async fetchOptions() {
      const postalCode = this.$wire.postalCode || this.postalCode;
      const houseNumber = this.$wire.houseNumber || this.houseNumber;
      const suffix = this.$wire.houseNumberSuffix || this.houseNumberSuffix || '';

      if (!postalCode || !houseNumber) return;

      this.isLoading = true;
      this.error = null;
      this.renderSlides();

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
        if (!response.ok) throw new Error('API request failed');

        const data = await response.json();
        const deliveryData = data?.data?.delivery || data?.delivery || [];

        if (!deliveryData.length) {
          this.error = 'Geen bezorgopties gevonden voor dit adres.';
          this.isLoading = false;
          this.renderSlides();
          return;
        }

        this.options = this.formatDeliveryOptions(deliveryData);
        this.isLoading = false;

        this.renderSlides(true);
        this.initSwiper();

        if (!preservedDate || !this.restoreSelection(preservedDate)) {
          this.selectByIndex(0);
        }
      } catch (e) {
        console.error('Delivery options fetch error:', e);
        this.error = 'Kon bezorgtijden niet ophalen.';
        this.isLoading = false;
        this.renderSlides();
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

          const displayDate = daysDiff === 0 ? 'vandaag'
            : daysDiff === 1 ? `morgen`
            : daysDiff === 2 ? `overmorgen`
            : dayName;

          const timeSlot = (option.time || []).find(slot => (slot.type || 2) === 2);
          if (!timeSlot) return null;

          return {
            date: option.date,
            display_date: displayDate,
            date_string: date.toLocaleDateString('nl-NL', { day: 'numeric', month: 'long' }),
            time_string: `${timeSlot.start.slice(0, -3)} - ${timeSlot.end.slice(0, -3)}`,
            start: timeSlot.start,
            end: timeSlot.end,
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

      this.updateSlideStyles();
      this.$wire.updateSelection(this.selection);
    },

    restoreSelection(dateToFind) {
      const date = dateToFind || this.selection?.date;
      if (!date) return false;

      const index = this.findIndexByDate(date);
      if (index < 0) return false;

      this.selectByIndex(index);
      this.swiper?.slideTo(index, 0);
      return true;
    },

    findIndexByDate(date) {
      const search = date.substring(0, 10);
      return this.options.findIndex(o => o.date.substring(0, 10) === search);
    },
  };
}
</script>
