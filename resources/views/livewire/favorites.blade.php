<div
  class="container mx-auto px-4 py-12"
  x-data="{ loaded: false }"
  x-init="
    $wire.loadFavorites(favorites);
    loaded = true;
    $watch('favorites', (value) => $wire.loadFavorites(value));
  "
  x-on:remove-favorite.window="toggleFavorite($event.detail.productId)"
>
  <x-page-header class="mb-4" title="Favorieten" />

  {{-- Loading State --}}
  <div x-show="!loaded" x-cloak class="bg-white rounded-lg p-12 shadow-sm text-center">
    @svg('resources.images.icons.loader', 'animate-spin h-12 w-12 mx-auto text-red-600 mb-4')
    <p class="text-gray-600">{{ __('Favorieten laden...', 'sage') }}</p>
  </div>

  <div x-show="loaded" x-cloak>
    @if ($this->isEmpty)
      {{-- Empty State --}}
      <div class="bg-white rounded-lg p-12 shadow-sm text-center">
        <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
          />
        </svg>
        <h2 class="text-2xl font-bold font-heading mb-4">{{ __('Je hebt nog geen favorieten', 'sage') }}</h2>
        <p class="text-gray-600 mb-8">{{ __('Voeg producten toe aan je favorieten door op het hartje te klikken.', 'sage') }}</p>
        <a
          href="{{ wc_get_page_permalink('shop') }}"
          class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold font-heading py-4 px-8 rounded-lg uppercase transition duration-200"
        >
          {{ __('Bekijk Producten', 'sage') }}
        </a>
      </div>
    @else
      <p class="text-gray-600 mb-8">
        {{ sprintf(_n('%d product in je favorieten', '%d producten in je favorieten', count($this->products), 'sage'), count($this->products)) }}
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach ($this->products as $product)
          <div wire:key="favorite-{{ $product->id }}" class="relative">
            <x-woocommerce.product :product="$product" :addToFavorites="false" />
            <button
              type="button"
              wire:click="removeFromFavorites({{ $product->id }})"
              class="absolute top-3 right-3 z-10 p-2 rounded-full bg-white shadow-sm hover:bg-red-50 transition-colors group"
              title="{{ __('Verwijderen uit favorieten', 'sage') }}"
            >
              @svg('resources.images.icons.heart', 'size-5 text-red-600 fill-red-600 group-hover:scale-110 transition-transform')
            </button>
          </div>
        @endforeach
      </div>

      {{-- Continue Shopping --}}
      <x-icon-link class="mt-8" :href="home_url()">Verder winkelen</x-icon-link>
    @endif
  </div>
</div>
