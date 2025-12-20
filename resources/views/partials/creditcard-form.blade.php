@php
  $inputClasses = 'w-full py-3 px-4 rounded-sm border border-gray-300 outline-none focus:ring-1 ring-red-600 shadow-xs relative bg-white isolate';
@endphp

<div
  id="creditcard-form"
  class="flex flex-col gap-4"
  x-data="creditcardForm()"
  wire:ignore
>
  <div>
    <label for="card-number" class="block mb-1.5 text-sm font-medium">Kaartnummer</label>
    <div id="card-number" class="{{ $inputClasses }}">
      <div id="card-number-placeholder" class="absolute -z-10 top-1/2 left-4 transform -translate-y-1/2 text-gray-900 opacity-50">1234 5678 9012 3456</div>
    </div>
    <div class="mt-1.5 empty:mt-0 text-sm text-red-500" id="card-number-error"></div>
  </div>

  <div>
    <label for="card-holder" class="block mb-1.5 text-sm font-medium">Kaarthouder</label>
    <div id="card-holder" class="{{ $inputClasses }}">
      <div id="card-holder-placeholder" class="absolute -z-10 top-1/2 left-4 transform -translate-y-1/2 text-gray-900 opacity-50">Naam op kaart</div>
    </div>
    <div class="mt-1.5 empty:mt-0 text-sm text-red-500" id="card-holder-error"></div>
  </div>

  <div class="grid grid-cols-2 gap-3">
    <div>
      <label for="expiry-date" class="block mb-1.5 text-sm font-medium">Vervaldatum</label>
      <div id="expiry-date" class="{{ $inputClasses }}"></div>
      <div class="mt-1.5 empty:mt-0 text-sm text-red-500" id="expiry-date-error"></div>
    </div>

    <div>
      <label for="verification-code" class="block mb-1.5 text-sm font-medium">Beveiligingscode</label>
      <div id="verification-code" class="{{ $inputClasses }}">
        <div id="verification-code-placeholder" class="absolute -z-10 top-1/2 left-4 transform -translate-y-1/2 text-gray-900 opacity-50">CVC</div>
        <svg class="absolute -z-10 top-1/2 right-4 transform -translate-y-1/2" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="var(--colorIconCardCvc)" role="img" aria-labelledby="cvcDesc"><path opacity=".2" fill-rule="evenodd" clip-rule="evenodd" d="M15.337 4A5.493 5.493 0 0013 8.5c0 1.33.472 2.55 1.257 3.5H4a1 1 0 00-1 1v1a1 1 0 001 1h16a1 1 0 001-1v-.6a5.526 5.526 0 002-1.737V18a2 2 0 01-2 2H3a2 2 0 01-2-2V6a2 2 0 012-2h12.337zm6.707.293c.239.202.46.424.662.663a2.01 2.01 0 00-.662-.663z"></path><path opacity=".4" fill-rule="evenodd" clip-rule="evenodd" d="M13.6 6a5.477 5.477 0 00-.578 3H1V6h12.6z"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M18.5 14a5.5 5.5 0 110-11 5.5 5.5 0 010 11zm-2.184-7.779h-.621l-1.516.77v.786l1.202-.628v3.63h.943V6.22h-.008zm1.807.629c.448 0 .762.251.762.613 0 .393-.37.668-.904.668h-.235v.668h.283c.565 0 .95.282.95.691 0 .393-.377.66-.911.66-.393 0-.786-.126-1.194-.37v.786c.44.189.88.291 1.312.291 1.029 0 1.736-.526 1.736-1.288 0-.535-.33-.967-.88-1.14.472-.157.778-.573.778-1.045 0-.738-.652-1.241-1.595-1.241a3.143 3.143 0 00-1.234.267v.77c.378-.212.763-.33 1.132-.33zm3.394 1.713c.574 0 .974.338.974.778 0 .463-.4.785-.974.785-.346 0-.707-.11-1.076-.337v.809c.385.173.778.26 1.163.26.204 0 .392-.032.573-.08a4.313 4.313 0 00.644-2.262l-.015-.33a1.807 1.807 0 00-.967-.252 3 3 0 00-.448.032V6.944h1.132a4.423 4.423 0 00-.362-.723h-1.587v2.475a3.9 3.9 0 01.943-.133z"></path></svg>
      </div>
      <div class="mt-1.5 empty:mt-0 text-sm text-red-500" id="verification-code-error"></div>
    </div>
  </div>

  <input type="hidden" name="card_token" />
</div>

@pushonce('scripts')
<script>
  function creditcardForm() {
    return {
      mollie: null,
      components: {},
      mounted: false,

      fields: {
        cardNumber: 'card-number',
        cardHolder: 'card-holder',
        expiryDate: 'expiry-date',
        verificationCode: 'verification-code',
      },

      options: {
        styles: {
          base: {
            lineHeight: '1.5',
            fontSize: '1rem',
            '::placeholder': {
              color: 'rgba(36, 40, 47, 0.5)',
            },
          },
        },
      },

      init() {
        this.mollie = getMollieInstance();
        if (!this.mollie) {
          setTimeout(() => this.init(), 100);
          return;
        }

        this.mountComponents();
      },

      mountComponents() {
        if (this.mounted) return;

        Object.entries(this.fields).forEach(([type, id]) => {
          this.mountComponent(type, id);
        });

        this.mounted = true;
      },

      mountComponent(type, id) {
        const component = this.mollie.createComponent(type, this.options);
        this.components[type] = component;
        component.mount(`#${id}`);
        this.setupEventHandlers(type, id);
      },

      setupEventHandlers(type, id) {
        const errorEl = document.getElementById(`${id}-error`);
        const placeholderEl = document.getElementById(`${id}-placeholder`);
        const fieldEl = document.getElementById(id);

        // Handle placeholder visibility
        if (placeholderEl) {
          this.components[type].addEventListener('change', (event) => {
            placeholderEl.style.opacity = event.dirty ? '0' : '0.5';
          });
        }

        this.components[type].addEventListener('blur', (event) => {

          if(!event.dirty) return;

          if (event.valid) {
            fieldEl.classList.remove('border-red-500');
          } else if (event.touched) {
            fieldEl.classList.add('border-red-500');
          }

          if (errorEl) {
            errorEl.textContent = (event.error && event.touched) ? event.error : '';
          }
        });
      },

      destroy() {
        Object.values(this.components).forEach((component) => {
          if (component && typeof component.unmount === 'function') {
            component.unmount();
          }
        });
        this.components = {};
        this.mounted = false;
      },
    };
  }
</script>
<script>
  let mollieInstance = null;
  function getMollieInstance() {
    if (!mollieInstance && typeof Mollie !== 'undefined') {
      mollieInstance = Mollie(@js(config('services.mollie.profile_id')), {
        locale: 'nl_NL',
        testmode: @js(config('services.mollie.test_mode')),
      });
    }
    return mollieInstance;
  }
</script>
<script src="https://js.mollie.com/v1/mollie.js" onload="getMollieInstance()"></script>
@endpushonce
