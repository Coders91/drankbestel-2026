@props(['query'])

<div {{ $attributes->merge(['class' => 'search-no-results text-center py-12']) }}>
    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-gray-300 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="11" cy="11" r="8"/>
        <path d="m21 21-4.35-4.35"/>
        <path d="M8 8l6 6M14 8l-6 6"/>
    </svg>
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Geen resultaten gevonden', 'sage') }}</h3>
    <p class="text-gray-500">
        {{ sprintf(__('We konden niets vinden voor "%s"', 'sage'), $query) }}
    </p>
    <p class="text-sm text-gray-400 mt-2">
        {{ __('Probeer een andere zoekterm of bekijk onze categorieën', 'sage') }}
    </p>
</div>
