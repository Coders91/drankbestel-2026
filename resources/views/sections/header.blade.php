<header class="banner sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">
  <div class="container mx-auto px-4">
    <div class="flex items-center justify-between h-16">
      {{-- Logo --}}
      <a class="brand text-xl font-bold font-heading text-gray-900 hover:text-red-600 transition-colors" href="{{ home_url('/') }}">
        {!! $siteName !!}
      </a>

      @if (has_nav_menu('primary_navigation'))
        <nav class="nav-primary hidden md:flex" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
          {{-- {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav flex gap-6', 'echo' => false]) !!} --}}
        </nav>
      @endif

      {{-- Icons --}}
      <div class="flex items-center gap-1">
        {{-- Favorites Icon (Alpine.js for live updates from localStorage) --}}
        <a
          href="{{ route('favorites') }}"
          class="relative p-2 text-gray-700 hover:text-red-600 transition-colors"
          title="{{ __('Favorieten', 'sage') }}"
        >
          @svg('resources.images.icons.heart')
          <span
            x-show="favorites.length > 0"
            x-text="favorites.length > 99 ? '99+' : favorites.length"
            x-cloak
            class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold rounded-full min-w-5 h-5 flex items-center justify-center px-1"
          ></span>
        </a>

        {{-- Cart Icon (Livewire for live updates) --}}
        <livewire:cart-icon />
      </div>
    </div>
  </div>
</header>
