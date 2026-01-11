@props([
    'term' => [],
    'taxonomy' => '',
])

@php
    $isActive = $term['active'] ?? false;
    $isDisabled = ($term['count'] ?? 0) === 0;
    $inputId = 'filter-' . $taxonomy . '-' . ($term['slug'] ?? '');
@endphp

<div class="py-1 {{ $isDisabled ? 'opacity-50' : '' }}">
    <x-checkbox
        :withErrors="false"
        :id="$inputId"
        :name="$taxonomy"
        :checked="$isActive"
        :disabled="$isDisabled"
        @change="$dispatch('filter-apply', { url: '{{ $term['url'] }}' })"
        class="text-sm"
    >
      <x-filters.item :count="$term['count']">{{ $term['label'] }}</x-filters.item>
    </x-checkbox>
</div>
