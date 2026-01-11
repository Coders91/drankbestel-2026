@use('App\Support\Search\SearchHighlighter')
@props(['category', 'compact' => false, 'query' => ''])

<a href="{{ $category->url }}" {{ $attributes->merge(['class' => 'search-result-item search-result-category flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors']) }}>
    @if($category->imageId)
        <div class="search-result-image shrink-0">
            {!! wp_get_attachment_image($category->imageId, 'thumbnail', false, ['class' => 'w-10 h-10 object-cover rounded']) !!}
        </div>
    @else
        <div class="search-result-image shrink-0 w-10 h-10 bg-gray-100 rounded flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9,22 9,12 15,12 15,22"/>
            </svg>
        </div>
    @endif
    <div class="search-result-content flex-1 min-w-0">
        <h4 class="search-result-title font-medium text-sm">
            @if($query)
                {!! SearchHighlighter::highlight($category->name, $query) !!}
            @else
                {{ $category->name }}
            @endif
        </h4>
        @if(!$compact)
            <p class="search-result-meta text-xs text-gray-500">{{ $category->count }} {{ _n('product', 'producten', $category->count, 'sage') }}</p>
        @endif
    </div>
</a>
