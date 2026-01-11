<div>
  <a
    href="{{ route('cart') }}"
    class="relative p-2 text-gray-700 hover:text-red-600 transition-colors flex"
    title="{{ __('Winkelwagen', 'sage') }}"
  >
    @svg('resources.images.icons.shopping-cart')
    @if ($this->itemCount > 0)
      <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold rounded-full min-w-5 h-5 flex items-center justify-center px-1">
        {{ $this->itemCount > 99 ? '99+' : $this->itemCount }}
      </span>
    @endif
  </a>
</div>
