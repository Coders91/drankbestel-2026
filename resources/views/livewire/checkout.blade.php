<div>

<div class="min-h-screen grid lg:grid-cols-[1fr_.66fr] xl:grid-cols-[1fr_.5fr]">
  {{-- Left side: Form --}}
  <div class="bg-white py-12 px-4 lg:px-8">
    <form id="checkout" class="grid gap-y-8 lg:gap-12 max-w-3xl mx-auto" name="checkout" method="post" novalidate x-data="checkout()" @submit.prevent="submitForm()" @place-order.window="event.target.submit()">
    <x-page-header title="Afrekenen"></x-page-header>
      {{-- Order Error Message --}}
      @error('order')
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="text-red-700 font-medium">{{ $message }}</p>
          </div>
        </div>
      @enderror
      <div class="min-w-0 shadow-xs">
        <x-checkout-section
          title="Persoonlijke gegevens"
          title-class="mb-2"
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
      </div>

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
          <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          Bestelling plaatsen..
        </span>
      </x-button>
    </form>
  </div>

  {{-- Right side: Order Review --}}
  <aside class="bg-gray-700/3 py-12 px-8 border-l border-gray-200 lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto">
    <x-icon-link class="text-sm" :href="route('cart')">Winkelwagen aanpassen</x-icon-link>
    <x-checkout-order-review />
  </aside>
</div>
</div>

@pushonce('scripts')
  <script>
    function checkout() {
      return {
        form: @json($form),
        rules: @json($form->rules()),
        messages: @json($form->messages()),
        errors: {},
        touched: {},
        billing_loading: false,
        shipping_loading: false,

        validateField(el, includeTouched = false) {
          const fieldName = typeof el === 'string' ? el : el.name;
          const element = typeof el === 'string'
            ? document.getElementById(el) || document.querySelector(`[name="${el}"]`)
            : el;

          if (!this.touched[fieldName] && !includeTouched) {
            return true;
          }

          const rulesString = this.rules[fieldName];
          const value = element ? element.value : this.form[fieldName];
          let hasError = false;

          this.errors[fieldName] = '';

          if (!rulesString) return;

          const rules = rulesString.split('|');

          for (let rule of rules) {
            let [ruleName, param] = rule.split(':');

            if (ruleName === 'required_if') {
              const [targetField, targetValue] = param.split(',');

              const left  = String(this.form[targetField]).toLowerCase();
              const right = String(targetValue).toLowerCase();

              if (left === right) {
                if (value === null || value === undefined || value === '') {
                  this.addError(fieldName, ruleName);
                  hasError = true;
                  break;
                }
              }

              continue;
            }

            if (this.runRule(ruleName, value, param) === false) {
              this.addError(fieldName, ruleName);
              hasError = true;
              break;
            }
          }
          return !hasError;
        },

        runRule(ruleName, value, param) {
          if (value === '' || value === null || value === false) {
            return ruleName !== 'required' && ruleName !== 'accepted';
          }

          switch (ruleName) {
            case 'required':
              return value !== undefined && value !== '';
            case 'min':
              if (typeof value === 'string') {
                return value.length >= parseInt(param);
              }
              if (Array.isArray(value)) {
                return value.length >= parseInt(param);
              }
              if (!isNaN(value)) {
                return parseFloat(value) >= parseFloat(param);
              }
              return false;
            case 'alpha':
              return /^[a-zA-Z]+$/.test(value);
            case 'boolean':
              return /^true|false|1|0+$/.test(value);
            case 'numeric':
              return /^\d+$/.test(value);
            case 'email':
              return /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(value);
            case 'accepted':
              return value === true || value === 'true' || value === 1 || value === '1' || value === 'yes' || value === 'on';
            case 'regex':
              return this.regex(value, param);
            default:
              return true;
          }
        },

        regex(value, param) {
          const invalidModifiers = ['x', 's', 'u', 'X', 'U', 'A'];

          let pattern = param;
          let modifiers = '';

          if (param.startsWith('/') && param.lastIndexOf('/') > 0) {
            const lastSlash = param.lastIndexOf('/');
            pattern = param.slice(1, lastSlash);
            modifiers = param.slice(lastSlash + 1);
            modifiers = [...modifiers].filter(m => !invalidModifiers.includes(m)).join('');
          }
          const jsRegex = new RegExp(pattern, modifiers);

          return jsRegex.test(value);
        },

        validateAll() {
          let isValid = true;
          this.errors = {};

          for (const fieldName in this.rules) {
            let field = document.getElementById(fieldName);

            if (!field || !field.name) {
              field = document.querySelector(`[name="${fieldName}"]`);
            }

            // Skip validation when hidden or read only
            if (field && field.offsetParent !== null) {
              if (!this.validateField(field, true)) {
                isValid = false;
              }
            }
          }

          if (!isValid) {
            const errorFields = Object.keys(this.errors).filter(key => this.errors[key] && this.errors[key].length > 0);
            const form = document.getElementById('checkout');
            const formElements = [...form.elements];

            const firstErrorEl = formElements.find(el =>
              el.name && errorFields.includes(el.name) && el.offsetParent !== null
            );

            if (firstErrorEl) {
              firstErrorEl.focus();
            }
          }

          return isValid;
        },

        async submitForm() {
          if(this.validateAll()) {

            let token = '';
            if(this.form.payment_method === 'mollie_creditcard') {
              const result = await mollieInstance.createToken();

              if(result.token) {
                token = result.token;
              }

              if (result.error) {
                // Trigger and show errors somehow
                console.error(result.error);
                return false;
              }
            }
            this.$wire.save(token);
          }
        },

        addError(fieldName, ruleName) {
          const messageKey = `${fieldName}.${ruleName}`;
          this.errors[fieldName] = this.messages[messageKey] || 'Dit veld is ongeldig';
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
