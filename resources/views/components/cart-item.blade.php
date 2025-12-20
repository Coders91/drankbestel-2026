@php
  /** @var App\View\Models\CartItem $item */
@endphp

@props([
  'item' => null,
])

<div
  {{ $attributes->merge(['class' => 'relative bg-white border border-gray-100 shadow-sm rounded-lg p-4 mb-6']) }}
  wire:key="{{ $item->key }}"
  data-quantity="{{ $item->quantity }}"
  data-max-quantity="{{ $item->maxQuantity }}"
  x-data="{
    loading: false,
    removing: false,
    get atMin() { return parseInt(this.$el.dataset.quantity) <= 1 },
    get atMax() { return parseInt(this.$el.dataset.quantity) >= parseInt(this.$el.dataset.maxQuantity) }
  }"
>
  {{-- Remove overlay with spinner --}}
  <div
    x-show="removing"
    x-cloak
    class="absolute inset-0 bg-white/80 rounded-lg flex items-center justify-center z-10"
  >
    <svg class="animate-spin h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
  </div>

  <div class="flex flex-col sm:flex-row gap-3">
    <a href="{{ $item->product->url }}" class="block shrink-0 flex justify-center md:min-w-40">
      <x-image class="w-auto h-40 py-1 object-contain rounded-lg" alt="{{ $item->product->title }}" id="{{ $item->product->imageId }}" />
    </a>
    <div class="flex-1">
      <div class="flex justify-between items-start mb-2">
        <div>
          <div class="flex flex-grow  justify-between">
          <a href="{{ $item->product->url }}" class="hover:text-red-600 transition">
            <h3 class="text-lg font-semibold font-heading">{{ $item->product->name }}</h3>
          </a>
          </div>
          @if ($item->product->contents)
            <p class="text-gray-600">{{ $item->product->contents }}</p>
          @endif
        </div>
        <button
          type="button"
          x-on:click="removing = true; $wire.removeItem('{{ $item->key }}')"
          x-bind:disabled="removing"
          class="text-gray-400 hover:text-red-600 transition p-1 disabled:opacity-50"
          title="{{ __('Verwijderen', 'sage') }}"
        >
          @svg('resources.images.icons.trash-01')
        </button>
      </div>

      @if (! $item->isInStock)
        <p class="text-red-600 text-sm mb-2">{{ __('Niet op voorraad', 'sage') }}</p>
      @endif

      <div class="flex items-end justify-between mt-4">
        {{-- Quantity Controls --}}
        <div class="flex items-center border border-gray-300 rounded-lg shadow-xs">
          <button
            type="button"
            x-on:click="loading = 'decrease'; $wire.decreaseQuantity('{{ $item->key }}').then(() => loading = false)"
            x-bind:disabled="loading || atMin"
            class="w-10 h-10 text-sm flex items-center justify-center rounded-lg hover:bg-gray-50 transition disabled:opacity-50"
          >
            <span class="sr-only">{{ __('Verlaag aantal', 'sage') }}</span>
            <span x-show="loading !== 'decrease'">
              @svg('resources.images.icons.minus', 'size-4')
            </span>
            <svg x-show="loading === 'decrease'" x-cloak class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
          </button>
          <span class="w-10 h-10 text-sm flex items-center justify-center border-x border-gray-300 text-center">
                      {{ $item->quantity }}
                    </span>
          <button
            type="button"
            x-on:click="loading = 'increase'; $wire.increaseQuantity('{{ $item->key }}').then(() => loading = false)"
            x-bind:disabled="loading || atMax"
            class="w-10 h-10 text-sm flex items-center justify-center rounded-lg hover:bg-gray-50 transition disabled:opacity-50"
          >
            <span class="sr-only">{{ __('Verhoog aantal', 'sage') }}</span>
            <span x-show="loading !== 'increase'">
              @svg('resources.images.icons.plus', 'size-4')
            </span>
            <svg x-show="loading === 'increase'" x-cloak class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
          </button>
        </div>
        <span class="text-base pr-4 font-medium font-heading text-gray-900">{{ $item->lineTotal->formatted() }}</span>
      </div>
    </div>
  </div>
</div>
