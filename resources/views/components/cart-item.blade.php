@php
  /** @var App\View\Models\CartItem $item */
@endphp

@props([
  'item' => null,
  'message' => null,
])

<div
  {{ $attributes->merge(['class' => 'relative bg-white border border-gray-200 shadow-sm rounded-lg p-4 mb-6']) }}
  wire:key="{{ $item->key }}"
  data-quantity="{{ $item->quantity }}"
  data-max-quantity="{{ $item->maxQuantity }}"
  data-min-quantity="{{ $item->minQuantity }}"
  data-step="{{ $item->packSize }}"
  x-data="{
    loading: false,
    removing: false,
    get atMin() { return parseInt(this.$el.dataset.quantity) <= parseInt(this.$el.dataset.minQuantity) },
    get atMax() { return parseInt(this.$el.dataset.quantity) >= parseInt(this.$el.dataset.maxQuantity) }
  }"
>
  {{-- Remove overlay with spinner --}}
  <div
    x-show="removing"
    x-cloak
    class="absolute inset-0 bg-white/80 rounded-lg flex items-center justify-center z-10"
  >
    @svg('resources.images.icons.loader', 'animate-spin h-8 w-8 text-red-600')
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
            <h3 class="text-lg font-semibold">{{ $item->product->name }}</h3>
          </a>
          </div>
          @if ($item->product->contents)
            <p class="text-gray-600">{{ $item->product->contents }}</p>
          @endif
          @if ($item->soldAsPack)
            <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded">
              {{ sprintf(__('%d-pack', 'sage'), $item->packSize) }}
            </span>
          @endif
        </div>
        <button
          type="button"
          x-on:click="removing = true; $wire.removeItem('{{ $item->key }}')"
          x-bind:disabled="removing"
          class="text-gray-700 hover:text-red-600 transition p-1 disabled:opacity-50"
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
            <span x-show="loading === 'decrease'" x-cloak>
              @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
            </span>
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
            <span x-show="loading === 'increase'" x-cloak>
              @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
            </span>
          </button>
        </div>
        @if($item->product->is_on_sale)
          <div class="flex flex-col">
            <span class="text-base pr-4 font-medium font-heading line-through text-gray-700">{{ $item->lineRegularTotal->formatted() }}</span>
            <span class="font-semibold text-red-600 text-base">{{ $item->lineSubtotal->formatted() }}</span>
          </div>
        @else
          <span class="text-base pr-4 font-medium font-heading text-gray-900">{{ $item->lineSubtotal->formatted() }}</span>
        @endif
      </div>

      {{-- Per-item quantity messages --}}
      @if($message)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-3 py-2 mt-3 text-sm">
          {{ $message }}
        </div>
      @endif

      <div x-show="atMax" x-cloak class="text-amber-600 text-sm mt-2">
        {{ __('Maximale hoeveelheid bereikt', 'sage') }}
      </div>
    </div>
  </div>
</div>
