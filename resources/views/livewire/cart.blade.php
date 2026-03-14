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
    <p class="text-gray-600 mb-8">
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
          <h2 class="text-xl font-semibold mb-4">{{ __('Overzicht', 'sage') }}</h2>
          <div class="space-y-2 mb-6">
            {{-- Subtotal before discounts (only show if there are discounts) --}}
            @if ($this->totals->discount->amount->amount > 0)
              <div class="flex justify-between text-gray-800">
                <span class="text-gray-900">{{ __('Totaal producten ' . '(' . $this->totals->itemCount . ')', 'sage') }}</span>
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
                <span class="text-gray-900">{{ __('Totaal producten ' . '(' . $this->totals->itemCount . ')', 'sage') }}</span>
                <span class="">{{ $this->totals->subtotal->amount->formatted() }}</span>
              </div>
            @endif

            {{-- Shipping --}}
            <div class="flex justify-between pb-2 text-gray-800">
              <span class="text-gray-900">{{ __('Verzending', 'sage') }}</span>
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
          <x-button class="w-full" wire:click="proceedToCheckout">Afrekenen</x-button>

          {{-- Coupon Code --}}
          <x-coupon-form class="mt-4" :coupons="$this->coupons" :messages="$messages" />
          <div class="pt-4 mt-4 border-t border-gray-200">
            @include('partials.payment-icons')
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
