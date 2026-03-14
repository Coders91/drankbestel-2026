@props([
  'coupons' => [],
  'messages' => [],
])

<div {{ $attributes }}>
  @if (count($coupons) > 0)
    {{-- Applied Coupons --}}
    <h3 class="text-gray-900 text-lg font-heading mb-3 font-semibold flex items-center gap-2">
      {{ __('Toegepaste kortingscodes', 'sage') }}
    </h3>
    @foreach ($coupons as $coupon)
      <div class="group flex items-center justify-between bg-green-50 border border-green-200/60 rounded-lg px-3 py-2.5 mb-2 transition-colors hover:bg-green-100/60">
        <div class="flex items-center gap-2">
          @svg('resources.images.icons.check-circle', 'size-4 text-green-600 shrink-0')
          <span class="font-semibold text-green-800 text-sm">{{ strtoupper($coupon->code) }}</span>
          <span class="text-green-600 text-sm font-medium">-{{ $coupon->amountFormatted }}</span>
        </div>
        <button
          type="button"
          wire:click="removeCoupon('{{ $coupon->code }}')"
          class="text-green-400 hover:text-red-500 transition-colors p-0.5 rounded"
          title="{{ __('Verwijderen', 'sage') }}"
        >
          @svg('resources.images.icons.x', 'size-5')
        </button>
      </div>
    @endforeach
  @else
    {{-- Coupon Code Input (Accordion) --}}
    <x-accordion>
      <x-slot:title class="font-semibold text-lg">
        {{ __('Kortingscode', 'sage') }}
      </x-slot:title>

      <div class="pt-3" x-data="couponForm()">
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
            class="relative"
            wire:loading.attr="disabled"
            wire:target="applyCoupon"
          >
            <span wire:loading.class="invisible" wire:target="applyCoupon">
              {{ __('Toepassen', 'sage') }}
            </span>
            <span wire:loading.flex wire:target="applyCoupon" x-cloak class="absolute inset-0 items-center justify-center">
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
    </x-accordion>

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
