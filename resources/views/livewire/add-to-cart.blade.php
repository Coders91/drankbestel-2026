<div
  x-data="{ showError: false, errorMessage: '' }"
  x-on:add-to-cart-with-qty.window="if ($event.detail.productId === {{ $productId }}) { $wire.add($event.detail.quantity) }"
  x-on:add-to-cart-pack.window="if ($event.detail.productId === {{ $productId }}) { $wire.addPack($event.detail.multiplier || 1) }"
  x-on:add-to-cart-error.window="if ($event.detail.productId === {{ $productId }}) { showError = true; errorMessage = $event.detail.message; setTimeout(function() { showError = false }, 4000) }"
  x-on:product-added-to-cart.window="if ($event.detail.productId === {{ $productId }}) { showError = false }"
  class="relative max-lg:flex-grow {{ !$this->isCard ? 'w-full' : 'lg:w-fit' }} "
>
  {{-- Error tooltip --}}
  <div
    x-show="showError"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-1"
    x-cloak
    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-50"
  >
    <div class="bg-red-600 text-white text-sm px-3 py-2 rounded-lg shadow-lg whitespace-nowrap max-w-xs text-center">
      <span x-text="errorMessage"></span>
      <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-red-600"></div>
    </div>
  </div>

  @if ($this->soldAsPack)
      <x-button
        wire:click="addPack"
        wire:loading.attr="disabled"
        wire:target="addPack"
        class="w-full h-full relative"
        :disabled="$this->disabled"
        :size="!$this->isCard ? 'regular' : ''"
      >
        <span wire:loading.class="invisible" wire:target="addPack">
           @if($this->disabled)
            {{ __('Uitverkocht', 'sage') }}
          @else
            {{ __('Bestel', 'sage') }} ({{ $this->packSize }})
          @endif
        </span>
        <span wire:loading.flex wire:target="addPack" class="absolute inset-0 items-center justify-center">
          @svg('resources.images.icons.loader', 'animate-spin size-4')
        </span>
      </x-button>
  @else
    {{-- Normal individual sale --}}
    <x-button
      wire:click="add"
      wire:loading.attr="disabled"
      wire:target="add"
      class="w-full h-full text-sm relative"
      :size="!$this->isCard ? 'regular' : ''"
      :disabled="$this->disabled"
    >
      <span wire:loading.class="invisible" wire:target="add">
        @if($this->disabled)
          @svg('resources.images.icons.slash-circle-01')
        @else
          {{ __('Bestel', 'sage') }}
        @endif
      </span>
      <span wire:loading.flex wire:target="add" class="absolute inset-0 items-center justify-center">
        @svg('resources.images.icons.loader', 'animate-spin size-4')
      </span>
    </x-button>
  @endif
</div>
