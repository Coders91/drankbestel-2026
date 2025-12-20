<div>
  {{-- Toast Notification --}}
  @if ($showToast && $lastAdded)
    <div
      x-data="{ show: true }"
      x-init="setTimeout(() => { show = false; $wire.hideToast(); }, 5000)"
      x-show="show"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0 translate-y-2"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 translate-y-2"
      class="fixed bottom-4 right-4 z-50 w-full max-w-sm bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden"
    >
      {{-- Header --}}
      <div class="bg-green-50 px-4 py-3 flex items-center justify-between border-b border-green-100">
        <div class="flex items-center gap-2 text-green-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span class="font-semibold text-sm">{{ __('Toegevoegd aan winkelwagen', 'sage') }}</span>
        </div>
        <button
          type="button"
          x-on:click="show = false; $wire.hideToast()"
          class="text-gray-400 hover:text-gray-600 transition"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      {{-- Product Info --}}
      <div class="p-4 flex items-center gap-4">
        @if ($lastAdded['image'])
          <img
            src="{{ $lastAdded['image'] }}"
            alt="{{ $lastAdded['name'] }}"
            class="w-16 h-16 object-contain rounded-lg bg-gray-50"
          >
        @endif
        <div class="flex-1 min-w-0">
          <h4 class="font-semibold text-gray-900 truncate">{{ $lastAdded['name'] }}</h4>
          <p class="text-sm text-gray-500">{{ sprintf(__('Aantal: %d', 'sage'), $lastAdded['quantity']) }}</p>
        </div>
      </div>

      {{-- Cart Summary --}}
      <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
        <div class="flex items-center justify-between text-sm mb-3">
          <span class="text-gray-600">
            {{ sprintf(_n('%d product in winkelwagen', '%d producten in winkelwagen', $this->itemCount, 'sage'), $this->itemCount) }}
          </span>
          <span class="font-semibold text-gray-900">{!! $this->total !!}</span>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-2">
          <button
            type="button"
            wire:click="goToCart"
            class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
          >
            {{ __('Winkelwagen', 'sage') }}
          </button>
          <button
            type="button"
            wire:click="goToCheckout"
            class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
          >
            {{ __('Afrekenen', 'sage') }}
          </button>
        </div>
      </div>
    </div>
  @endif
</div>
