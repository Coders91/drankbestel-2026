<x-usp-bar class="z-20 bg-red-600 text-white text-sm" :usps="App\Services\UspService::headerUspsTitles()" />

{{-- Sticky Header Container --}}
<header
    class="z-20 bg-white transition-transform duration-300">
    {{-- Main Header Bar --}}
    <div class="border-b border-gray-100">
      <div class="container max-lg:px-0 flex align-content-center min-h-14 lg:min-h-20 pt-2 lg:py-3 mx-auto">
          <div
              class="grid grid-flow-col items-center gap-2 justify-between w-full">


            <div class="flex items-center gap-2">

            {{-- Hamburger Menu Button (mobile only) --}}
            <button
              type="button"
              class="lg:hidden pl-4 p-2 text-gray-700 hover:text-red-600 transition-colors"
              title="{{ __('Menu', 'sage') }}"
              @click="openMobileMenu()"
              aria-label="{{ __('Open menu', 'sage') }}"
            >
              <svg aria-hidden="true" class="w-6 h-6
            max-lg:group-data-[header-style=transparent]:stroke-white" width="20" height="14" viewBox="0 0 20 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 7H15M1 1H19M1 13H19"></path>
              </svg>
            </button>
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
                  class="max-sm:row-start-2 max-sm:col-span-2 w-full md:max-w-2xl"
              >
                  <livewire:header-search class="-ml-2 -mr-1" />
              </div>

              {{-- Right Side: Icons & Hamburger --}}
              <div class="flex items-center md:gap-2 pr-4">

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
