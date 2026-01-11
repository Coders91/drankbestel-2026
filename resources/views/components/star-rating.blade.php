<div {{ $attributes->merge(['class' => 'flex']) }}>
    @for ($i = 1; $i <= 5; $i++)
        @if ($rating >= $i)
            @svg('resources.images.icons.star-full', $sizeClass())
        @elseif ($rating >= $i - 0.5)
            @svg('resources.images.icons.star-half', $sizeClass())
        @else
            @svg('resources.images.icons.star-empty', $sizeClass())
        @endif
    @endfor
</div>
