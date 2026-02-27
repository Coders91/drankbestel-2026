<div>
  <a
    href="{{ route('cart') }}"
    class="relative p-2 text-gray-700 hover:text-red-600 transition-colors flex"
    title="{{ __('Winkelwagen', 'sage') }}"
  >
    @svg('resources.images.icons.shopping-cart', 'h-6')
    @if ($this->itemCount > 0)
      <x-counter-badge>
        {{ $this->itemCount > 99 ? '99+' : $this->itemCount }}
      </x-counter-badge>
    @endif
  </a>
</div>
