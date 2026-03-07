<div
  id="applepay-form"
  x-data="applePayForm()"
  x-init="init()"
>
  {{-- Informational text when Apple Pay is available (button is in the order review sidebar) --}}
  <div x-show="available" x-cloak>
    <p class="text-sm text-gray-600">
      Gebruik de Apple Pay knop om uw bestelling te plaatsen.
    </p>
  </div>
</div>

<style>
  body:not(.has-applepay) [data-payment-gateway="mollie_applepay"],
  body:not(.has-applepay) [data-payment-gateway="applepay"] {
    display: none !important;
  }

  .apple-pay-button {
    -webkit-appearance: -apple-pay-button;
    -apple-pay-button-type: buy;
    display: inline-block;
    width: 100%;
    min-height: 44px;
    border: none;
    background-color: black;
    cursor: pointer;
    border-radius: 4px;
  }
  .apple-pay-button-black {
    -apple-pay-button-style: black;
  }
  .apple-pay-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
</style>

@pushonce('scripts')
<script>
  function applePayForm() {
    return {
      available: false,
      checked: false,
      processing: false,

      init() {
        const notifyAvailability = (available) => {
          this.available = available;
          this.checked = true;
          if (available) document.body.classList.add('has-applepay');
          window.dispatchEvent(new CustomEvent('apple-pay-availability', { detail: { available } }));
        };

        // Check if Apple Pay is available
        if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
          // Check if we can make payments with specific networks
          ApplePaySession.canMakePaymentsWithActiveCard('merchant.nl.drankbestel')
            .then((canMake) => {
              notifyAvailability(canMake || ApplePaySession.canMakePayments());
            })
            .catch(() => {
              // Fallback: just check basic capability
              notifyAvailability(ApplePaySession.canMakePayments());
            });
        } else {
          notifyAvailability(false);
        }

        // Listen for trigger from the sidebar Apple Pay button
        window.addEventListener('trigger-apple-pay', () => {
          if (this.available) this.startPayment();
        });
      },

      async startPayment() {
        if (this.processing) return;
        this.processing = true;

        try {
          // Get order total from the page
          const totalElement = document.querySelector('[data-checkout-total]');
          const total = totalElement ? totalElement.dataset.checkoutTotal : '0.00';

          const request = {
            countryCode: 'NL',
            currencyCode: 'EUR',
            merchantCapabilities: ['supports3DS'],
            supportedNetworks: ['amex', 'maestro', 'masterCard', 'visa', 'vPay'],
            total: {
              label: 'Drankbestel.nl',
              type: 'final',
              amount: total
            }
          };

          const session = new ApplePaySession(3, request);

          // Merchant validation - get session from our endpoint
          session.onvalidatemerchant = async (event) => {
            try {
              const response = await fetch('/wp-json/mollie/v1/applepay/session', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                },
                body: JSON.stringify({ validationUrl: event.validationURL })
              });

              if (!response.ok) {
                throw new Error('Failed to validate merchant');
              }

              const merchantSession = await response.json();
              session.completeMerchantValidation(merchantSession);
            } catch (error) {
              console.error('Merchant validation failed:', error);
              session.abort();
              this.processing = false;
            }
          };

          // Payment authorized - get the token and submit to Livewire
          session.onpaymentauthorized = (event) => {
            const token = JSON.stringify(event.payment.token);

            // Call Livewire to process the payment
            this.$wire.saveApplePayPayment(token)
              .then((result) => {
                session.completePayment(ApplePaySession.STATUS_SUCCESS);
              })
              .catch((error) => {
                console.error('Payment failed:', error);
                session.completePayment(ApplePaySession.STATUS_FAILURE);
                this.processing = false;
              });
          };

          // Payment cancelled
          session.oncancel = () => {
            this.processing = false;
          };

          session.begin();

        } catch (error) {
          console.error('Apple Pay error:', error);
          this.processing = false;
        }
      }
    };
  }
</script>
@endpushonce
