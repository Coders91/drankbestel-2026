@php
  /** @var App\View\Models\CartItem $item */
@endphp

@props([
  'item' => null,
  'message' => null,
])

<div
  {{ $attributes->merge(['class' => 'relative bg-white border-b md:border border-gray-200 md:rounded-lg pb-4 md:p-6 not-last:mb-4 md:mb-8']) }}
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

  <div class="flex flex-row gap-4 md:gap-6">
    <a href="{{ $item->product->url }}" class="block shrink-0 flex justify-center min-w-16 md:min-w-28">
      <x-image class="w-auto h-30 md:h-34 object-contain rounded-lg" alt="{{ $item->product->title }}" id="{{ $item->product->imageId }}" />
    </a>
    <div class="flex-1">
      <div class="flex md:flex-wrap justify-between items-start md:items-center mb-2">
        <div>
          <a href="{{ $item->product->url }}" class="flex-shrink-1 hover:text-red-600 transition">
            <h3 class="text-base md:text-lg font-heading font-semibold">{{ $item->product->name }}</h3>
          </a>
          @if ($item->product->contents)
            <p class="text-sm leading-6 text-gray-600">{{ $item->product->contents }}</p>
          @endif
          @if ($item->soldAsPack)
            <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded">
              {{ sprintf(__('%d-pack', 'sage'), $item->packSize) }}
            </span>
          @endif
        </div>
        <div class="flex items-center gap-2 md:gap-6">
          <button
            type="button"
            x-on:click.prevent="toggleFavorite({{ $item->product->id }})"
            x-bind:title="isFavorite({{ $item->product->id }}) ? '{{ __('Verwijderen uit favorieten', 'sage') }}' : '{{ __('Toevoegen aan favorieten', 'sage') }}'"
            class="max-md:p-1"
          >
            <span x-bind:class="isFavorite({{ $item->product->id }}) ? '*:text-red-600 *:fill-red-600' : '*:text-gray-800 *:hover:text-red-600'">
              @svg('resources.images.icons.heart', 'transition-colors')
            </span>
          </button>
          <button
            type="button"
            x-on:click="removing = true; $wire.removeItem('{{ $item->key }}')"
            x-bind:disabled="removing"
            class="text-gray-700 hover:text-red-600 transition max-md:p-1 disabled:opacity-50 md:mr-2"
            title="{{ __('Verwijderen', 'sage') }}"
          >
            @svg('resources.images.icons.trash-01')
          </button>
        </div>
      </div>

      @if (! $item->isInStock)
        <p class="text-red-600 text-sm mb-2">{{ __('Niet op voorraad', 'sage') }}</p>
      @endif

      <div class="flex flex-wrap items-center justify-between gap-4 mt-2.5">
        {{-- Quantity Controls --}}
        <div class="flex items-center border border-gray-400 rounded-lg shadow-xs">
          <button
            type="button"
            x-on:click="loading = 'decrease'; $wire.decreaseQuantity('{{ $item->key }}').then(() => loading = false)"
            x-bind:disabled="loading || atMin"
            class="size-9 text-sm flex items-center justify-center rounded-lg hover:bg-gray-50 transition disabled:opacity-50"
          >
            <span class="sr-only">{{ __('Verlaag aantal', 'sage') }}</span>
            <span x-show="loading !== 'decrease'">
              @svg('resources.images.icons.minus', 'size-3')
            </span>
            <span x-show="loading === 'decrease'" x-cloak>
              @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
            </span>
          </button>
          <span class="size-9 text-sm flex items-center justify-center border-x border-gray-400 text-center font-semibold">
                      {{ $item->quantity }}
                    </span>
          <button
            type="button"
            x-on:click="loading = 'increase'; $wire.increaseQuantity('{{ $item->key }}').then(() => loading = false)"
            x-bind:disabled="loading || atMax"
            class="size-9 text-sm flex items-center justify-center rounded-lg hover:bg-gray-50 transition disabled:opacity-50"
          >
            <span class="sr-only">{{ __('Verhoog aantal', 'sage') }}</span>
            <span x-show="loading !== 'increase'">
              @svg('resources.images.icons.plus', 'size-3')
            </span>
            <span x-show="loading === 'increase'" x-cloak>
              @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
            </span>
          </button>
        </div>

        @if($isNextDayDelivery)
          <p class="flex items-center gap-2 order-3 md:basis-full text-sm text-green-600 font-medium leading-6 truncate">@svg('resources.images.icons.clock', 'hidden md:block size-4') Voor 18:00 besteld, Morgen in huis</p>
        @endif

        @if($item->product->is_on_sale)
          <div class="flex flex-col">
            <span class="text-lg pr-4 font-semibold font-heading line-through text-gray-700">{{ $item->lineRegularTotal->formatted() }}</span>
            <span class="font-semibold text-red-600 text-lg">{{ $item->lineSubtotal->formatted() }}</span>
          </div>
        @else
          <span class="md:text-lg md:pr-4 font-semibold font-heading text-gray-900">{{ $item->lineSubtotal->formatted() }}</span>
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
