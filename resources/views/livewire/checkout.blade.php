<div>
  <x-checkout-header />
  <div class="container px-4">
    <x-page-header class="flex lg:justify-center mt-8 mb-4" title="Afrekenen" />
    <form method="post"
          id="checkout"
          name="checkout"
          class="grid lg:grid-cols-[768px_1fr] gap-y-8 lg:gap-y-12 lg:gap-x-8"
          x-data="checkout()"
          @submit.prevent="submitForm()"
          @pageshow.window="$event.persisted && window.location.reload()"
    >
      {{-- Left side: Form sections --}}
      <div class="grid gap-y-8 lg:gap-y-12 min-w-0">
        {{-- Order Error Message --}}
        @error('order')
          <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
              <p class="text-red-700 font-medium">{{ $message }}</p>
            </div>
          </div>
        @enderror

        <x-checkout-section
          title="Persoonlijke gegevens"
          titleClass="mb-2"
          x-text="form.is_business_order ? 'Zakelijke gegevens' : 'Persoonlijke gegevens'"
        >
          <x-slot:header>
            {{-- Buttons to toggle business fields --}}
            <div class="flex gap-2.5 mb-2">
              <div class="px-5 py-2.5 border border-gray-300 rounded-lg bg-white">
                <x-radio id="consumer"
                         name="is_business_order"
                         value="0"
                         class="text-sm text-gray-600"
                         @change="form.is_business_order = false"
                         x-model="form.is_business_order"
                         wire:model.boolean.live="form.is_business_order"
                >
                  Particulier
                </x-radio>
              </div>
              <div class="px-5 py-2.5 border border-gray-300 rounded-lg bg-white">
                <x-radio id="business"
                         name="is_business_order"
                         value="1"
                         class="text-sm text-gray-600"
                         @change="form.is_business_order = true"
                         x-model="form.is_business_order"
                         wire:model.boolean.live="form.is_business_order"
                >
                  Zakelijk
                </x-radio>
              </div>
            </div>
          </x-slot:header>

          <div class="grid gap-4">
            @include('partials.business-fields')
            @include('partials.billing-fields')
          </div>
        </x-checkout-section>

        <x-checkout-section title="Verzendadres">
          @include('partials.shipping-fields')
        </x-checkout-section>

        <x-checkout-section title="Bezorgmoment">
          <livewire:delivery-options
            :postalCode="$form->billing_postcode"
            :houseNumber="$form->billing_house_number"
            :houseNumberSuffix="$form->billing_house_number_suffix"
            wire:model="deliverySelection"
          />
        </x-checkout-section>

        <x-checkout-section title="Betaalmethoden">
          @include('woocommerce.checkout.payment-options')
        </x-checkout-section>

        <x-button
          hidden
          type="submit"
          class="flex items-center"
          wire:loading.attr="disabled"
          wire:loading.class="opacity-50 cursor-not-allowed"
          wire:target="save"
        >
          <span wire:loading.remove wire:target="save">Bestelling plaatsen</span>
          <span wire:loading wire:target="save" class="flex items-center gap-2">
            @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
            Bestelling plaatsen..
          </span>
        </x-button>
      </div>

      {{-- Right side: Order Review --}}
      <aside class="h-fit">
        <x-checkout-order-review class="bg-white border border-gray-300 p-6 rounded-xl" />
      </aside>
    </form>
    <x-checkout-footer class="-mx-4 px-4" />
  </div>
</div>

@pushonce('scripts')
  <script>
    function checkout() {
      return {
        // Use global form validator
        ...formValidator({
          form: @json($form),
          rules: @json($form->rules()),
          messages: @json($form->messages())
        }),

        // Checkout-specific state
        billing_loading: false,
        shipping_loading: false,

        // Checkout-specific methods
        async submitForm() {
          if (this.validateAll('checkout')) {
            let token = '';
            if (this.form.payment_method === 'mollie_creditcard') {
              const result = await mollieInstance.createToken();

              if (result.token) {
                token = result.token;
              }

              if (result.error) {
                console.error(result.error);
                return false;
              }
            }
            this.$wire.save(token);
          }
        },

        async getAddressData(type, el) {
          this.validateField(el);

          const postcode = document.getElementById(`${type}_postcode`).value.trim();
          const houseNumber = document.getElementById(`${type}_house_number`).value.trim();
          const suffixEl = document.getElementById(`${type}_house_number_suffix`);
          const suffix = suffixEl ? suffixEl.value.trim() : '';

          if (!postcode || !houseNumber) {
            this.form[`${type}_address_found`] = false;

            // clear fields
            this.form[`${type}_street_name`] = '';
            this.form[`${type}_city`] = '';

            this.errors[`${type}_street_name`] = '';
            this.errors[`${type}_city`] = '';

            return;
          }

          this[`${type}_loading`] = true;

          try {
            const result = await this.$wire.validateAddress(type, postcode, houseNumber, suffix || null);

            if (!result) {
              this.form[`${type}_address_found`] = false;

              // clear fields
              this.form[`${type}_street_name`] = '';
              this.form[`${type}_city`] = '';

              // clear any previous errors (important)
              this.errors[`${type}_street_name`] = '';
              this.errors[`${type}_city`] = '';

              return;
            }

            // Address found
            this.form[`${type}_address_found`] = true;

            // Clear errors for autofilled fields
            this.errors[`${type}_street_name`] = '';
            this.errors[`${type}_city`] = '';

            if ((type === 'billing' && this.$wire.get('form.billing_address_found')) ||
              (type === 'shipping' && this.$wire.get('form.shipping_address_found'))) {
              this.$dispatch('fetch-delivery-options');
            }

          } catch (error) {
            console.error(`Address validation failed for ${type}:`, error);
          } finally {
            this[`${type}_loading`] = false;
          }
        }
      }
    }
  </script>
@endpushonce
