<div
  x-data="{
    spinning: false,
    call(promise) {
      this.spinning = true
      const start = Date.now()
      promise.finally(() => {
        const elapsed = Date.now() - start
        setTimeout(() => this.spinning = false, Math.max(0, 300 - elapsed))
      })
    }
  }"
  x-on:add-to-cart-with-qty.window="if ($event.detail.productId === {{ $productId }}) { call($wire.add($event.detail.quantity)) }"
  x-on:add-to-cart-pack.window="if ($event.detail.productId === {{ $productId }}) { call($wire.addPack($event.detail.multiplier || 1)) }"
  class="relative max-lg:flex-grow"
>
  @if ($this->soldAsPack)
      <x-button
        x-on:click="call($wire.addPack())"
        class="w-full h-full relative"
        :disabled="$this->disabled"
        :size="!$this->isSingleProduct ? 'small' : ''"
      >
        <span :class="spinning && 'invisible'">
          {{ __('Bestel', 'sage') }} ({{ $this->packSize }})
        </span>
        <span x-show="spinning" x-cloak class="absolute inset-0 flex items-center justify-center">
          @svg('resources.images.icons.loader', 'animate-spin size-4')
        </span>
      </x-button>
  @else
    <x-button
      x-on:click="call($wire.add())"
      class="w-full h-full relative"
      :size="!$this->isSingleProduct ? 'small' : ''"
      :disabled="$this->disabled"
    >
      <span :class="spinning && 'invisible'">
        {{ __('Bestel', 'sage') }}
      </span>
      <span x-show="spinning" x-cloak class="absolute inset-0 flex items-center justify-center">
        @svg('resources.images.icons.loader', 'animate-spin size-4')
      </span>
    </x-button>
  @endif
</div>
