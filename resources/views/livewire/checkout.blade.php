<div>
  <x-checkout-header />
  <div class="container">
    <div class="mt-6">
      <x-icon-link class="col-span-full w-fit" :href="route('cart')">Winkelwagen</x-icon-link>
    </div>
    <form method="post"
          id="checkout"
          name="checkout"
          class="pt-6 lg:pt-10 grid lg:grid-cols-[1fr_384px] xl:grid-cols-[768px_1fr] gap-y-8 lg:gap-y-12 lg:gap-x-8"
          x-data="checkout()"
          @submit.prevent="submitForm()"
          @pageshow.window="$event.persisted && window.location.reload()"
    >
      <div>
        {{-- Left side: Form sections --}}
        <div class="grid gap-y-8 lg:gap-y-10 min-w-0">
          {{-- Order Error Message --}}
          @error('order')
            <x-alert type="warning">
              <div class="flex items-center gap-3">
                @svg('resources.images.icons.alert-circle', 'w-5 h-5 text-red-500 shrink-0')
                <p class="text-red-700 font-medium">{{ $message }}</p>
              </div>
            </x-alert>
          @enderror

          @island
            <x-checkout-section
              title="Persoonlijke gegevens"
              titleClass="mb-3"
              x-text="form.is_business_order ? 'Zakelijke gegevens' : 'Persoonlijke gegevens'"
            >
              <x-slot:header>
                <div class="flex gap-4 mb-3 md:mb-4">
                  <div class="w-full md:w-fit px-4 py-2.5 border border-gray-300 rounded-lg bg-white">
                    <x-forms.radio id="consumer"
                             name="is_business_order"
                             value="0"
                             class="text-sm text-gray-600"
                             :checked="!$form->is_business_order"
                             @change="form.is_business_order = false"
                    >
                      Particulier
                    </x-forms.radio>
                  </div>
                  <div class="w-full md:w-fit px-4 py-2.5 border border-gray-300 rounded-lg bg-white">
                    <x-forms.radio id="business"
                             name="is_business_order"
                             value="1"
                             class="text-sm text-gray-600"
                             :checked="$form->is_business_order"
                             @change="form.is_business_order = true"
                    >
                      Zakelijk
                    </x-forms.radio>
                  </div>
                </div>
              </x-slot:header>

              <div class="grid gap-4">
                @include('partials.business-fields')
                @include('partials.billing-fields')
              </div>
            </x-checkout-section>
          @endisland

          <x-checkout-section title="Verzendadres">
            @include('partials.shipping-fields')
          </x-checkout-section>

          @island
            <x-checkout-section title="Bezorgmoment">
              <livewire:delivery-options
                :postalCode="$form->billing_postcode"
                :houseNumber="$form->billing_house_number"
                :houseNumberSuffix="$form->billing_house_number_suffix"
                wire:model="deliverySelection"
              />
            </x-checkout-section>
          @endisland

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
            <span wire:loading.flex wire:target="save" class="items-center gap-2">
              @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
              Bestelling plaatsen..
            </span>
          </x-button>
        </div>
      </div>

      {{-- Right side: Order Review --}}
      <aside class="lg:sticky lg:top-6 h-fit">
        <x-checkout-order-review class="bg-white md:border md:border-gray-300 md:px-6 md:pb-6 md:pt-5 md:rounded-xl" />
      </aside>
    </form>
    <x-checkout-footer class="-mx-4 px-4" />
  </div>
</div>

@pushonce('scripts')
  <script>
    function checkout() {
      return {
        // Client side validation
        ...formValidator({
          form: @json($form),
          rules: @json($form->rules()),
          messages: @json($form->messages())
        }),

        // Checkout-specific state
        billing_loading: false,
        shipping_loading: false,
        selectedPaymentMethod: @json($form->payment_method ?? ''),
        isBusinessOrder: @json($form->is_business_order),

        init() {
          // Sync Alpine selected payment method from Livewire
          this.$wire.$watch('form.payment_method', (value) => {
            this.selectedPaymentMethod = value;
          });
          // Sync Livewire business order state with Alpine
          this.$watch('form.is_business_order', (value) => {
            console.log('form.is_business_order', value);
            this.$wire.set('form.is_business_order', value);
          })
        },

        async submitForm() {
          if (this.validateAll('checkout')) {
            if (['applepay', 'mollie_applepay'].includes(this.selectedPaymentMethod)) {
              window.dispatchEvent(new CustomEvent('trigger-apple-pay'));
              return;
            }

            let token = '';
            if (this.selectedPaymentMethod === 'mollie_creditcard') {
              const result = await mollieInstance.createToken();

              if (result.token) {
                token = result.token;
              }

              if (result.error) {
                console.error(result.error);
                return false;
              }
            }
            this.$wire.save(token, this.form.is_business_order);
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

              // clear any previous errors
              this.errors[`${type}_street_name`] = '';
              this.errors[`${type}_city`] = '';

              return;
            }

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
