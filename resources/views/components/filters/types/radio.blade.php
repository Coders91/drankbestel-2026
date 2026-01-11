@props([
    'term' => [],
    'taxonomy' => '',
])

@php
    $isActive = $term['active'] ?? false;
    $isDisabled = ($term['count'] ?? 0) === 0;
    $inputId = 'filter-radio-' . $taxonomy . '-' . ($term['slug'] ?? '');
@endphp

<div class="py-1">
    <x-radio
        autocomplete="off"
        :id="$inputId"
        :name="'filter-' . $taxonomy"
        :value="$term['slug'] ?? ''"
        :checked="$isActive"
        :disabled="$isDisabled"
        @change="$dispatch('filter-apply', { url: '{{ $term['url'] }}' })"
        class="text-sm"
    >
      <x-filters.item :count="$term['count']">{{ $term['label'] }}</x-filters.item>
    </x-radio>
</div>
