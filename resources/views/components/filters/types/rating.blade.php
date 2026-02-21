@props([
    'term' => [],
    'taxonomy' => '',
])

@php
    $isActive = $term['active'] ?? false;
    $isDisabled = ($term['count'] ?? 0) === 0;
    $inputId = 'filter-rating-' . $taxonomy . '-' . ($term['slug'] ?? '');

    // Extract rating number from slug (e.g., "rated-5" => 5)
    $ratingValue = 0;
    if (preg_match('/rated-(\d+)/', $term['slug'] ?? '', $matches)) {
        $ratingValue = (int) $matches[1];
    }
@endphp

<div class="py-1">

  <x-forms.radio
    :id="$inputId"
    :name="'filter-' . $taxonomy"
    :value="$term['slug'] ?? ''"
    :checked="$isActive"
    :disabled="$isDisabled"
    @change="$dispatch('filter-apply', { url: '{{ $term['url'] }}' })"
  >
    <x-filters.item :count="$term['count']">

        <x-star-rating
                :rating="$ratingValue"
                size="sm"
            />

            @if ($ratingValue > 0 && $ratingValue < 5)
              & hoger
            @endif
    </x-filters.item>
  </x-forms.radio>
</div>
