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
    <div class="pt-4 px-4 md:px-6 md:pt-5">
      <h3 class="block text-lg font-heading text-gray-900 font-semibold text-balance line-clamp-2">{{ $product->name }}</h3>
      <div class="text-base text-gray-600 font-medium">{{ $product->contents }}</div>
    </div>
  </a>
  <div class="align-self-end flex justify-between pt-3 pb-6 px-6">
    @if ($product->price->is_on_sale)
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
<livewire:add-to-cart :product-id="$product->id" />
  </div>
</article>
