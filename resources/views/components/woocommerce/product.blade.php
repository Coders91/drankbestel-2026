@php
  /** @var App\View\Models\Product $product */
@endphp

@props([
  'id' => null,
  'product' => null,
  'addToFavorites' => true,
])

<article class="card relative grid h-full">
  @if ($addToFavorites)
    <button
      type="button"
      x-on:click.prevent="toggleFavorite({{ $product->id }})"
      class="absolute top-3 right-3 flex justify-center items-center size-10 p-1 z-10 rounded-full bg-white/80 hover:bg-white shadow-sm transition-colors"
      x-bind:title="isFavorite({{ $product->id }}) ? '{{ __('Verwijderen uit favorieten', 'sage') }}' : '{{ __('Toevoegen aan favorieten', 'sage') }}'"
    >
      <span x-bind:class="isFavorite({{ $product->id }}) ? '*:text-red-600 *:fill-red-600' : '*:text-gray-800 *:hover:text-red-600'">
        @svg('resources.images.icons.heart', 'transition-colors')
      </span>
    </button>
  @endif

  <a href="{{ $product->url }}">
    <div class="bg-gray-50 py-6 rounded-t-xl">
      <x-image height="160" width="80" :id="$product->imageId" class="w-full max-h-50 object-contain mix-blend-multiply" />
    </div>
    <div class="pt-5 px-4 md:px-5">
      <h3 class="block text-lg stext-gray-900 font-semibold text-balance line-clamp-2">{{ $product->name }}</h3>
      <div class="text-base font-heading text-gray-600 pt-1">{{ $product->contents }}</div>
      @if ($product->reviewCount > 0)
        <div class="flex items-center gap-1.5 pt-2 pb-1">
          <x-star-rating :rating="$product->rating" class="gap-1" size="sm" />
          <span class="text-sm text-gray-500">({{ $product->reviewCount }})</span>
        </div>
      @endif
    </div>
  </a>
  <div class="flex items-center justify-between self-end h-fit px-4 md:px-5 pb-4 md:pb-5">
    @if ($product->is_on_sale)
      <div class="flex flex-col font-heading">
        <span class="text-gray-700 line-through text-muted">
          {{ $product->price->regular->formatted() }}
        </span>

        <span class="font-semibold text-red-600 text-xl">
          {{ $product->price->sale->formatted() }}
        </span>
      </div>
    @else
      <span class="text-xl font-semibold font-heading">
      {{ $product->price->regular->formatted() }}
    </span>
    @endif
    <livewire:add-to-cart :disabled="!$product->is_in_stock" :productId="$product->id" />
  </div>
</article>
