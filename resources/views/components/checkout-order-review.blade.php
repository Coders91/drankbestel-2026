@php
  /** @var App\View\Models\CartItem $item */
  /** @var App\View\Models\CartTotals $totals */
@endphp

<div {{ $attributes->merge(['class' => 'grid content-center m-auto']) }}>
  <h2 class="text-gray-900 text-xl mb-4 font-semibold">Overzicht</h2>

  {{-- Order Items --}}
  <div class="space-y-4">
    @foreach ($items as $item)
      <div class="flex gap-3 pb-4 border-b border-gray-200 last:border-b-0">
        <div class="shrink-0 w-26 h-26 bg-white rounded-lg border border-gray-100 flex items-center justify-center">
          <x-image
            class="w-full h-full object-contain p-1 rounded-lg"
            alt="{{ $item->product->title }}"
            id="{{ $item->product->imageId }}"
          />
        </div>
        <div class="flex-1 min-w-0">
          <h3 class="font-medium text-gray-900 truncate">{{ $item->product->name }}</h3>
          @if ($item->product->contents)
            <p class="text-sm text-gray-500">{{ $item->product->contents }}</p>
          @endif
          <div class="flex items-center justify-between mt-1">
            <span class="text-sm text-gray-500">{{ $item->quantity }}x</span>
            <span class="font-medium text-gray-900">{{ $item->lineSubtotal->formatted() }}</span>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Totals --}}
  <div class="space-y-3 pt-4 border-t border-gray-200">
    {{-- Subtotal before discounts (only show if there are discounts) --}}
    @if ($totals->discount->amount->amount > 0)
      <div class="flex justify-between text-gray-900">
        <span>{{ __('Subtotaal', 'sage') }} ({{ $totals->itemCount }} {{ $totals->itemCount === 1 ? 'product' : 'producten' }})</span>
        <span>{{ $totals->subtotalBeforeDiscounts->amount->formatted() }}</span>
      </div>
      {{-- Discount --}}
      <div class="flex justify-between text-gray-900">
        <span>{{ __('Korting', 'sage') }}</span>
        <span class="text-green-600">-{{ $totals->discount->amount->formatted() }}</span>
      </div>
    @else
      {{-- Subtotal (no discounts) --}}
      <div class="flex justify-between text-gray-900">
        <span>{{ __('Subtotaal', 'sage') }} ({{ $totals->itemCount }} {{ $totals->itemCount === 1 ? 'product' : 'producten' }})</span>
        <span>{{ $totals->subtotal->amount->formatted() }}</span>
      </div>
    @endif

    {{-- Shipping --}}
    <div class="flex justify-between text-gray-900">
      <span>{{ __('Verzending', 'sage') }}</span>
      <span>
        @if (is_string($totals->shippingDisplay))
          <span class="text-green-600">{{ $totals->shippingDisplay }}</span>
        @else
          {{ $totals->shippingDisplay->amount->formatted() }}
        @endif
      </span>
    </div>

    {{-- Total --}}
    <div class="flex justify-between items-center pt-4" data-checkout-total="{{ number_format($totals->total->amount->decimal(), 2, '.', '') }}">
      <span class="text-lg font-semibold font-heading text-gray-900">{{ __('Totaal', 'sage') }}</span>
      <span class="text-lg font-semibold font-heading text-gray-900">{{ $totals->total->amount->formatted() }}</span>
    </div>
  </div>

  {{-- Checkboxes --}}
  <div class="space-y-4 pt-4 mt-6 border-t border-gray-200">
    {{-- Age Check --}}
    <div>
      <x-checkbox
        id="age_check"
        name="age_check"
        x-model="form.age_check"
        wire:model="form.age_check"
        @change="markTouched('age_check'); validateField($el)"
      >
        <span class="text-gray-700">Ik bevestig dat ik 18 jaar of ouder ben</span> <span class="text-red-600">*</span>
      </x-checkbox>
    </div>

    {{-- Newsletter --}}
    <div>
      <x-checkbox
        id="newsletter"
        name="newsletter"
        x-model="form.newsletter"
        wire:model="form.newsletter"
      >
        <span class="text-gray-700">Ja, ik wil graag meer drankaanbiedingen ontvangen.</span>
      </x-checkbox>
    </div>

    <x-button
      type="submit"
      class="mt-10 flex items-center w-full text-lg"
      wire:loading.attr="disabled"
      wire:loading.class="opacity-50 cursor-not-allowed"
      wire:target="save"
      size="regular"
    >
      <span wire:loading.remove wire:target="save">Bestelling plaatsen</span>
      <span wire:loading.flex wire:target="save" class="flex items-center gap-2">
        @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
        Bestelling plaatsen..
      </span>
    </x-button>
  </div>
</div>
