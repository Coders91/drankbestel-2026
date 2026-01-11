@props(['title', 'count' => null])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2 mb-4 pb-2 border-b border-gray-200']) }}>
    <h3 class="text-lg font-semibold">{{ $title }}</h3>
    @if($count !== null)
        <span class="text-sm text-gray-700">({{ $count }})</span>
    @endif
</div>
