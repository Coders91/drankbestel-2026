@php
  /** @var App\View\Models\CartItem $item */
  /** @var App\View\Models\CartTotals $totals */
@endphp

<div {{ $attributes->merge(['class' => 'grid content-center m-auto']) }}>
  <h2 class="text-gray-900 text-xl mb-4 font-semibold">Bestelling</h2>
  {{-- Order Items --}}
  <div class="max-h-sm overflow-y-auto" x-data="{expanded: false}">
    @foreach ($visibleItems as $item)
      @include('partials.order-review-item')
    @endforeach
    @if($hiddenItems->isNotEmpty())
      <div x-show="expanded" x-cloak x-collapse>
        @foreach($hiddenItems as $item)
            @include('partials.order-review-item')
        @endforeach
      </div>
      <button type="button" @click="expanded = !expanded" class="group flex items-center gap-1 py-2 text-red-600 transition-colors">
        <span class="flex items-center gap-2 text-sm group-hover:text-red-600" x-text="expanded ? 'Toon minder producten' : 'Toon alle producten'">
          Toon alle producten
        </span>
          @svg('resources.images.icons.chevron-down', 'size-5 group-hover:stroke-red-600 transition-transform', [':class' => "{ 'rotate-180': expanded }"])
      </button>
    @endif
  </div>

  {{-- Totals --}}
  <div class="space-y-2 mt-2 pt-4 border-t border-gray-200">
    {{-- Subtotal before discounts (only show if there are discounts) --}}
    @if ($totals->discount->amount->amount > 0)
      <div class="flex justify-between text-gray-900">
        <span>{{ __('Totaal producten', 'sage') }} ({{ $totals->itemCount }})</span>
        <span>{{ $totals->subtotalBeforeDiscounts->amount->formatted() }}</span>
      </div>
      {{-- Discount --}}
      <div class="flex justify-between text-gray-900">
        <span>{{ __('Korting', 'sage') }}</span>
        <span class="text-green-600 font-medium">-{{ $totals->discount->amount->formatted() }}</span>
      </div>
    @else
      {{-- Subtotal (no discounts) --}}
      <div class="flex justify-between text-gray-900">
        <span>{{ __('Totaal producten', 'sage') }} ({{ $totals->itemCount }})</span>
        <span x-show="form.is_business_order" x-cloak>{{ $totals->subtotalExclTax }}</span>
        <span x-show="!form.is_business_order">{{ $totals->subtotal->amount->formatted() }}</span>
      </div>
      <div class="flex justify-between text-gray-900" x-show="form.is_business_order" x-cloak>
        <span>Btw</span>
        <span>{{ $totals->tax->formatted() }}</span>
      </div>
    @endif

    {{-- Shipping --}}
    <div class="flex justify-between text-gray-900">
      <span>{{ __('Verzending', 'sage') }}</span>
      <span>
        @if (is_string($totals->shippingDisplay))
          <span class="text-green-600 font-medium">{{ $totals->shippingDisplay }}</span>
        @else
          {{ $totals->shippingDisplay->amount->formatted() }}
        @endif
      </span>
    </div>

    {{-- Total --}}
    <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200" data-checkout-total="{{ number_format($totals->total->amount->decimal(), 2, '.', '') }}">
      <span class="text-lg font-semibold font-heading text-gray-900">{{ __('Totaal', 'sage') }}</span>
      <span>
        <span class="text-lg font-semibold font-heading text-gray-900">{{ $totals->total->amount->formatted() }}</span>
        <span class="block text-xs text-gray-600" x-show="form.is_business_order" x-cloak>inclusief btw</span>
      </span>
    </div>
  {{-- Checkboxes --}}
  <div class="space-y-4 pt-4 mt-4 border-t border-gray-200">
    {{-- Age Check --}}
    <div>
      <x-forms.checkbox
        id="age_check"
        name="age_check"
        x-model="form.age_check"
        wire:model="form.age_check"
        @change="markTouched('age_check'); validateField($el)"
      >
        <span class="text-gray-800">Ik bevestig dat ik 18 jaar of ouder ben</span>
      </x-forms.checkbox>
    </div>

    {{-- Newsletter --}}
    <div>
      <x-forms.checkbox
        id="newsletter"
        name="newsletter"
        x-model="form.newsletter"
        wire:model="form.newsletter"
      >
        <span class="text-gray-800">Ja, ik wil graag meer drankaanbiedingen ontvangen.</span>
      </x-forms.checkbox>
    </div>
  </div>

    <x-button
      type="submit"
      class="mt-4 flex items-center w-full"
      wire:loading.attr="disabled"
      wire:loading.class="opacity-50 cursor-not-allowed"
      wire:target="save"
    >
      <span wire:loading.remove wire:target="save">Bestelling plaatsen</span>
      <span wire:loading.flex wire:target="save" class="flex items-center gap-2">
        @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
        Bestelling plaatsen..
      </span>
    </x-button>
  </div>
</div>
