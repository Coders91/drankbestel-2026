@use('App\Support\Search\SearchHighlighter')
@props(['tag', 'compact' => false, 'query' => ''])

<a href="{{ $tag->url }}" {{ $attributes->merge(['class' => 'search-result-item search-result-tag inline-flex items-center gap-1 px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-full text-sm transition-colors']) }}>
    <span class="tag-name">
        @if($query)
            {!! SearchHighlighter::highlight($tag->name, $query) !!}
        @else
            {{ $tag->name }}
        @endif
    </span>
    @if(!$compact)
        <span class="tag-count text-xs text-gray-500">({{ $tag->count }})</span>
    @endif
</a>
