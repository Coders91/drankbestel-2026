@php use App\View\Models\Product; @endphp

<div class="container mx-auto px-4 py-12">
  <x-page-header class="mb-4" title="Winkelwagen" />
  @if ($this->isEmpty)
    {{-- Empty Cart State --}}
    <div class="bg-white rounded-lg p-12 shadow-sm text-center">
      <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
      </svg>
      <h2 class="text-2xl font-bold font-heading mb-4">{{ __('Je winkelwagen is leeg', 'sage') }}</h2>
      <p class="text-gray-600 mb-8">{{ __('Voeg producten toe aan je winkelwagen om door te gaan met winkelen.', 'sage') }}</p>
      <x-icon-link :href="home_url()">Verder winkelen</x-icon-link>
    </div>
  @else
    <p class="text-gray-600 mb-8">
      {{ sprintf(_n('%d product in uw winkelwagen', '%d producten in uw winkelwagen', $this->totals->itemCount, 'sage'), $this->totals->itemCount) }}
    </p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      {{-- Cart Items --}}
      <div class="lg:col-span-2">
        @foreach ($this->items as $item)
          <x-cart-item :item="$item" />
        @endforeach

        {{-- Messages --}}
        @if (isset($messages['quantity']))
          <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 mb-4">
            {{ $messages['quantity'] }}
          </div>
        @endif

        {{-- Continue Shopping --}}
        <x-icon-link :href="home_url()">Verder winkelen</x-icon-link>
      </div>

      {{-- Order Summary --}}
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg p-6 border border-gray-100 shadow-sm sticky top-4">
          <h2 class="text-gray-900 text-xl font-heading mb-4 font-semibold">{{ __('Overzicht', 'sage') }}</h2>

          <div class="space-y-4 mb-6">
            {{-- Subtotal --}}
            <div class="flex justify-between text-gray-600">
              <span class="text-gray-600">{{ __('Totaal producten ' . '(' . $this->totals->itemCount . ')', 'sage') }}</span>
              <span class="">{{ $this->totals->subtotal->amount->formatted() }}</span>
            </div>

            {{-- Shipping --}}
            <div class="flex justify-between text-gray-600">
              <span class="text-gray-600">{{ __('Verzending', 'sage') }}</span>
              <span class=" {{ $this->totals->hasFreeShipping ? 'text-green-600' : '' }}">
                @if (is_string($this->totals->shippingDisplay))
                  {{ $this->totals->shippingDisplay }}
                @else
                  {{ $this->totals->shippingDisplay->amount->formatted() }}
                @endif
              </span>
            </div>

            {{-- Discount (if any) --}}
            @if ($this->totals->discount->amount->amount > 0)
              <div class="flex justify-between text-green-600">
                <span>{{ __('Korting', 'sage') }}</span>
                <span class="font-medium">-{{ $this->totals->discount->amount->formatted() }}</span>
              </div>
            @endif

            {{-- Total --}}
            <div class="border-t border-gray-200 pt-4">
              <div class="flex justify-between items-center">
                <span class="text-lg font-semibold font-heading">{{ __('Totaal', 'sage') }}</span>
                <span class="text-lg font-semibold font-heading">{{ $this->totals->total->amount->formatted() }}</span>
              </div>
              <p class="text-sm text-gray-500 mt-1">{{ __('Inclusief BTW', 'sage') }}</p>
            </div>
          </div>

          {{-- Checkout Button --}}
          <x-button class="w-full font-semibold uppercase font-heading mb-5" wire:click="proceedToCheckout">Afrekenen @svg('resources.images.icons.arrow-right')</x-button>

          <div class="mb-6">
            @include('partials.payment-icons')
          </div>

          {{-- Free Shipping Progress --}}
          @if (!$this->freeShipping->qualifies)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-5 mb-4">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-gray-800">
                  {{ sprintf(__('Nog %s tot gratis verzending', 'sage'), $this->freeShipping->remainingFormatted->formatted()) }}
                </p>
                <span class="text-xs text-gray-600">{{ $this->freeShipping->percentage }}%</span>
              </div>
              {{-- Progress Bar --}}
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  class="bg-red-600 h-2.5 rounded-full transition-all duration-300"
                  style="width: {{ $this->freeShipping->percentage }}%"
                ></div>
              </div>
              <p class="text-xs text-gray-600 mt-2">
                {{ sprintf(__('Gratis verzending vanaf %s', 'sage'), $this->freeShipping->minimumFormatted->formatted()) }}
              </p>
            </div>
          @endif

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
            <div class="border-t border-gray-200 pt-4">
              <h3 class="text-gray-900 text-lg font-heading mb-4 font-semibold">{{ __('Kortingscode', 'sage') }}</h3>
              <form wire:submit="applyCoupon" class="flex gap-2">
                <input
                  type="text"
                  wire:model="couponCode"
                  placeholder="{{ __('Voer code in', 'sage') }}"
                  class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent"
                >
                <button
                  type="submit"
                  class="bg-gray-900 hover:bg-gray-800 text-white font-bold font-heading px-6 py-2 rounded-lg uppercase transition duration-200"
                >
                  OK
                </button>
              </form>

              @if (isset($messages['coupon_error']))
                <p class="text-red-600 text-sm mt-2">{{ $messages['coupon_error'] }}</p>
              @endif

              @if (isset($messages['coupon_success']))
                <p class="text-green-600 text-sm mt-2">{{ $messages['coupon_success'] }}</p>
              @endif
            </div>
          @endif
        </div>
      </div>
    </div>
  @endif
</div>
