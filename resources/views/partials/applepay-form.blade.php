<div id="applepay-form" x-data="applePayForm()" x-init="init()" class="hidden"></div>

<style>
  body:not(.has-applepay) [data-payment-gateway="mollie_applepay"],
  body:not(.has-applepay) [data-payment-gateway="applepay"] {
    display: none !important;
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

          session.onvalidatemerchant = async (event) => {
            try {
              const response = await fetch('/wp-json/mollie/v1/applepay/session', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ validationUrl: event.validationURL })
              });

              if (!response.ok) {
                throw new Error(`Merchant session endpoint returned ${response.status}`);
              }

              const merchantSession = await response.json();
              session.completeMerchantValidation(merchantSession);
            } catch (error) {
              console.error('[ApplePay] Merchant validation failed:', error);
              session.abort();
              this.processing = false;
            }
          };

          session.onpaymentauthorized = (event) => {
            const token = JSON.stringify(event.payment.token);

            this.$wire.saveApplePayPayment(token)
              .then(() => {
                session.completePayment(ApplePaySession.STATUS_SUCCESS);
              })
              .catch((error) => {
                console.error('[ApplePay] Payment failed:', error);
                session.completePayment(ApplePaySession.STATUS_FAILURE);
                this.processing = false;
              });
          };

          session.oncancel = () => {
            this.processing = false;
          };

          session.begin();

        } catch (error) {
          console.error('[ApplePay] Uncaught error in startPayment:', error);
          this.processing = false;
        }
      }
    };
  }
</script>
@endpushonce
