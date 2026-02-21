<x-usp-bar class="z-20 bg-red-600 text-white text-sm" :usps="App\Services\UspService::headerUspsTitles()" />

{{-- Sticky Header Container --}}
<header
    class="z-20 bg-white transition-transform duration-300">
    {{-- Main Header Bar --}}
    <div class="border-b border-gray-100">
      <div class="container flex align-content-center min-h-14 lg:min-h-20 pl-4 pr-3 pt-2 lg:py-3 mx-auto">
          <div
              class="grid grid-flow-col items-center justify-between w-full">

            <div class="flex items-center gap-6 md:gap-5 lg:gap-10">

              {{-- Logo --}}
              <a
                  class="brand flex-shrink-0 text-xl font-bold font-heading text-gray-900 hover:text-red-600 transition-all duration-300"
                  href="{{ home_url('/') }}"
              >
                  <span class="block lg:h-7.5">
                      @svg('resources.images.logos.drankbestel', 'h-5.5 lg:h-full w-auto lg:pr-4')
                  </span>
              </a>
            </div>

              {{-- Search Bar --}}
              <div
                  class="max-sm:row-start-2 max-sm:col-span-2 w-full lg:max-w-2xl"
              >
                  <livewire:header-search class="-ml-2 -mr-1" />
              </div>

              {{-- Right Side: Icons & Hamburger --}}
              <div class="flex items-center lg:gap-2.5">

                  {{-- Favorites Icon --}}
                  <a
                      href="{{ route('favorites') }}"
                      class="relative p-2 text-gray-700 hover:text-red-600 transition-colors"
                      title="{{ __('Favorieten', 'sage') }}">
                      @svg('resources.images.icons.heart')
                      <x-counter-badge
                          x-show="favorites.length > 0"
                          x-text="favorites.length > 99 ? '99+' : favorites.length"
                          x-cloak
                          ></x-counter-badge>
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
                  @svg('resources.images.icons.menu', 'size-6')
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
