<div class="container py-12">
    @if($query && strlen($query) >= 2)
        <header class="woocommerce-products-header mb-6">
            <h1 class="woocommerce-products-header__title page-title text-2xl font-heading font-bold">
                {{ __('Je hebt gezocht naar', 'sage') }}: <span class="text-gray-600">"{{ $query }}"</span>
            </h1>
            @if($searchResults && $searchResults->hasProducts())
                <p class="text-gray-600 mt-2">
                    {{ sprintf(_n('%d product gevonden', '%d producten gevonden', count($searchResults->products), 'sage'), count($searchResults->products)) }}
                </p>
            @endif
        </header>
    @endif

    @if($searchResults && $searchResults->hasProducts())
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($searchResults->products as $product)
                <x-product :product="$product" />
            @endforeach
        </div>
    @elseif($query && strlen($query) >= 2)
        <x-search.no-results :query="$query" />
    @else
        <div class="text-center py-12 text-gray-500">
            <p>{{ __('Voer een zoekterm in om producten te vinden.', 'sage') }}</p>
        </div>
    @endif
</div>
