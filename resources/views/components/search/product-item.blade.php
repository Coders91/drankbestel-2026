@use('App\Support\Search\SearchHighlighter')
@props(['product', 'compact' => false, 'query' => ''])

<a href="{{ $product->url }}" {{ $attributes->merge(['class' => 'flex gap-2']) }}>
    @if($product->imageId)
        <div class="shrink-0">
            <x-image :id="$product->imageId" class="size-20 object-contain" />
        </div>
    @endif
    <div class="flex-1 min-w-0">
        <h3 class="font-medium text-sm truncate">
            @if($query)
                {!! SearchHighlighter::highlight($product->title, $query) !!}
            @else
                {{ $product->title }}
            @endif
        </h3>
        <p class="mt-1 text-sm">
            @if($product->is_on_sale && $product->price->sale)
                <del class="text-gray-400">{{ $product->price->regular->formatted() }}</del>
                <ins class="text-red-600 font-medium no-underline">{{ $product->price->sale->formatted() }}</ins>
            @else
                <span class="font-medium">{{ $product->price->regular->formatted() }}</span>
            @endif
        </p>
    </div>
</a>
