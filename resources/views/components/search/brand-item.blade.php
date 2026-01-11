@use('App\Support\Search\SearchHighlighter')
@props(['brand', 'compact' => false, 'query' => ''])

<a href="{{ $brand->url }}" {{ $attributes->merge(['class' => 'search-result-item search-result-brand flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors']) }}>
    @if($brand->imageId)
        <div class="search-result-image shrink-0">
            {!! wp_get_attachment_image($brand->imageId, 'thumbnail', false, ['class' => 'w-10 h-10 object-contain']) !!}
        </div>
    @else
        <div class="search-result-image shrink-0 w-10 h-10 bg-gray-100 rounded flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                <line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
        </div>
    @endif
    <div class="search-result-content flex-1 min-w-0">
        <h4 class="search-result-title font-medium text-sm">
            @if($query)
                {!! SearchHighlighter::highlight($brand->name, $query) !!}
            @else
                {{ $brand->name }}
            @endif
        </h4>
        @if(!$compact)
            <p class="search-result-meta text-xs text-gray-500">{{ $brand->productCount }} {{ _n('product', 'producten', $brand->productCount, 'sage') }}</p>
        @endif
    </div>
</a>
