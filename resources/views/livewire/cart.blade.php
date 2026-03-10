@php use App\View\Models\Product; @endphp

<div class="container py-12">
  <x-page-header class="mb-4" title="Winkelwagen" />
  @if ($this->isEmpty)
    {{-- Empty Cart State --}}
    <x-empty-state
      icon="shopping-bag"
      :title="__('Je winkelwagen is leeg', 'sage')"
      :description="__('Voeg producten toe aan je winkelwagen om door te gaan met winkelen.', 'sage')"
    >
      <x-slot:action>
        <x-icon-link :href="home_url()" class="inline-flex">Verder winkelen</x-icon-link>
      </x-slot:action>
    </x-empty-state>
  @else
    <p class="text-gray-800 mb-8">
      {{ sprintf(_n('Je hebt %d product in je winkelwagen', 'Je hebt %d producten in je winkelwagen', $this->totals->itemCount, 'sage'), $this->totals->itemCount) }}
    </p>

    <div class="grid lg:grid-cols-[1fr_384px] xl:grid-cols-[768px_1fr] gap-6 md:gap-y-8 lg:gap-y-12 lg:gap-x-8">
      {{-- Cart Items --}}
      <div>
        @foreach ($this->items as $item)
          <x-cart-item :item="$item" :message="$messages['quantity_' . $item->key] ?? null" />
        @endforeach
      </div>

      {{-- Order Summary --}}
      <div>
        <div class="bg-white md:rounded-xl md:px-6 md:pb-6 md:pt-5 md:border md:border-gray-200 md:shadow-[0_2px_10px_rgba(0,0,0,0.04)] md:sticky md:top-4">
          <h2 class="text-gray-900 text-xl mb-4 font-semibold font-heading">{{ __('Overzicht', 'sage') }}</h2>
          <div class="space-y-2 mb-6">
            {{-- Subtotal before discounts (only show if there are discounts) --}}
            @if ($this->totals->discount->amount->amount > 0)
              <div class="flex justify-between text-gray-800">
                <span class="text-gray-800">{{ __('Totaal producten ' . '(' . $this->totals->itemCount . ')', 'sage') }}</span>
                <span class="">{{ $this->totals->subtotalBeforeDiscounts->amount->formatted() }}</span>
              </div>

              {{-- Discount --}}
              <div class="flex justify-between text-green-600">
                <span>{{ __('Korting', 'sage') }}</span>
                <span class="font-medium">-{{ $this->totals->discount->amount->formatted() }}</span>
              </div>
            @else
              {{-- Subtotal (no discounts) --}}
              <div class="flex justify-between text-gray-800">
                <span class="text-gray-800">{{ __('Totaal producten ' . '(' . $this->totals->itemCount . ')', 'sage') }}</span>
                <span class="">{{ $this->totals->subtotal->amount->formatted() }}</span>
              </div>
            @endif

            {{-- Shipping --}}
            <div class="flex justify-between pb-2 text-gray-800">
              <span class="text-gray-800">{{ __('Verzending', 'sage') }}</span>
              <span class=" {{ $this->totals->hasFreeShipping ? 'text-green-600 font-medium' : '' }}">
                @if (is_string($this->totals->shippingDisplay))
                  {{ $this->totals->shippingDisplay }}
                @else
                  {{ $this->totals->shippingDisplay->amount->formatted() }}
                @endif
              </span>
            </div>

            {{-- Total --}}
            <div class="border-t border-gray-200 pt-4">
              <div class="flex justify-between items-center">
                <span class="text-lg font-semibold font-heading">{{ __('Totaal', 'sage') }}</span>
                <span class="text-lg font-semibold font-heading">{{ $this->totals->total->amount->formatted() }}</span>
              </div>
            </div>
          </div>

          {{-- Checkout Button --}}
          <x-button class="w-full font-semibold uppercase font-heading mb-6" wire:click="proceedToCheckout">Afrekenen @svg('resources.images.icons.arrow-right')</x-button>

          <div class="mb-6">
            @include('partials.payment-icons')
          </div>

          {{-- Applied Coupons --}}
          @if (count($this->coupons) > 0)
            <div class="border-t border-gray-200 pt-4 mb-4">
              <h3 class="text-gray-900 text-lg font-heading mb-4 font-semibold">{{ __('Toegepaste kortingscodes', 'sage') }}</h3>
              @foreach ($this->coupons as $coupon)
                <div class="flex items-center justify-between bg-green-50 rounded-lg px-3 py-2 mb-2">
                  <div>
                    <span class="font-semibold text-green-800">{{ strtoupper($coupon->code) }}</span>
                    <span class="text-green-600 text-sm ml-2">-{{ $coupon->amountFormatted }}</span>
                  </div>
                  <button
                    type="button"
                    wire:click="removeCoupon('{{ $coupon->code }}')"
                    class="text-green-600 hover:text-red-600 transition"
                    title="{{ __('Verwijderen', 'sage') }}"
                  >
                    @svg('resources.images.icons.x', 'size-6')
                  </button>
                </div>
              @endforeach
            </div>
          @else
            {{-- Coupon Code Input --}}
            <div class="border-t border-gray-200 pt-4" x-data="couponForm()">
              <h3 class="text-gray-900 text-lg font-heading mb-4 font-semibold">{{ __('Kortingscode', 'sage') }}</h3>
              <form
                @submit.prevent="if(validateAll('couponForm')) $wire.applyCoupon()"
                id="couponForm"
                class="flex gap-2"
              >
                <x-forms.input-text
                  type="text"
                  wire:model="couponCode"
                  name="couponCode"
                  placeholder="{{ __('Voer code in', 'sage') }}"
                  class="flex-1"
                  x-bind:class="{'!border-red-600': errors.couponCode}"
                  @input="markTouched('couponCode')"
                  @blur="validateField($el)"
                />
                <x-button
                  type="submit"
                  variant="secondary"
                  size="small"
                  wire:loading.attr="disabled"
                  wire:target="applyCoupon"
                >
                  <span wire:loading.remove wire:target="applyCoupon">
                    {{ __('Invoeren', 'sage') }}
                  </span>
                  <span wire:loading.flex wire:target="applyCoupon" class="items-center gap-2">
                    @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
                  </span>
                </x-button>
              </form>

              <template x-if="errors.couponCode">
                <p x-text="errors.couponCode" class="text-red-600 text-sm mt-2"></p>
              </template>

              @if (isset($messages['coupon_error']))
                <p class="text-red-600 text-sm mt-2">{{ $messages['coupon_error'] }}</p>
              @endif

              @if (isset($messages['coupon_success']))
                <p class="text-green-600 text-sm mt-2">{{ $messages['coupon_success'] }}</p>
              @endif
            </div>

            @pushonce('scripts')
            <script>
              function couponForm() {
                return {
                  ...formValidator({
                    form: { couponCode: '' },
                    rules: { couponCode: 'required' },
                    messages: { 'couponCode.required': '{{ __('Voer een kortingscode in.', 'sage') }}' }
                  })
                };
              }
            </script>
            @endpushonce
          @endif
        </div>
      </div>
    </div>
  @endif
</div>
