<x-usp-bar class="z-20 bg-red-600 text-white text-sm" :usps="App\Services\UspService::headerUspsTitles()" />

{{-- Sticky Header Container --}}
<header
    class="sticky top-0 z-20 bg-white transition-transform duration-300"
    :class="scrolled ? 'shadow-md' : ''"
>
    {{-- Main Header Bar --}}
    <div class="border-b border-gray-100">
      <div class="container mx-auto px-6">
          <div
              class="flex items-center justify-between gap-4 transition-all duration-300"
              :class="scrolled ? 'h-14' : ' lg:h-20 py-2 lg:py-4'"
          >
              {{-- Logo --}}
              <a
                  class="brand flex-shrink-0 text-xl font-bold font-heading text-gray-900 hover:text-red-600 transition-all duration-300"
                  href="{{ home_url('/') }}"
              >
                  <span
                      class="block transition-all duration-300"
                      :class="scrolled ? 'h-6' : 'lg:h-8'"
                  >
                      @svg('resources.images.logos.drankbestel', 'h-5 lg:h-full w-auto')
                  </span>
              </a>

              {{-- Search Bar --}}
              <div
                  class="hidden md:block flex-1 max-w-2xl transition-all duration-300"
                  :class="scrolled ? 'max-w-xl' : 'max-w-2xl'"
              >
                  <livewire:header-search />
              </div>

              {{-- Right Side: Icons & Hamburger --}}
              <div class="flex items-center gap-2">
                  {{-- Mobile Search Toggle (shows on mobile) --}}
                  <button
                      type="button"
                      class="md:hidden p-2 text-gray-700 hover:text-red-600 transition-colors"
                      title="{{ __('Zoeken', 'sage') }}"
                      @click="$dispatch('toggle-mobile-search')"
                  >
                      @svg('resources.images.icons.search')
                  </button>

                  {{-- Favorites Icon --}}
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
                          class="absolute -top-0.5 -right-0.5 bg-red-600 text-white text-xs font-bold rounded-full min-w-5 h-5 flex items-center justify-center px-1"
                      ></span>
                  </a>

                  {{-- Cart Icon --}}
                  <livewire:header-cart />

                  {{-- Hamburger Menu Button (mobile only) --}}
                  <button
                      type="button"
                      class="lg:hidden p-2 text-gray-700 hover:text-red-600 transition-colors"
                      title="{{ __('Menu', 'sage') }}"
                      @click="openMobileMenu()"
                      aria-label="{{ __('Open menu', 'sage') }}"
                  >
                      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                      </svg>
                  </button>
              </div>
          </div>
      </div>
    </div>

    {{-- Mega Menu Navigation Bar (desktop only) --}}
    @if (has_nav_menu('primary_navigation'))
        <x-navigation.mega-menu
            name="primary_navigation"
            :featured-brands="$featuredBrands"
        />
    @endif
</header>

{{-- Mobile Menu --}}
<x-navigation.mobile-menu
    :mega-menu-categories="$megaMenuCategories"
    :featured-brands="$featuredBrands"
/>
