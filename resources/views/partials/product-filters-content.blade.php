{{-- Category Filter --}}
@if(count($this->availableCategories) > 0)
  <div class="border-b border-gray-200 pb-4">
    <h3 class="text-base font-semibold mb-4">{{ __('Categorieën', 'sage') }}</h3>

    <div class="space-y-2">
      @foreach($this->availableCategories as $category)
        <label
          class="flex items-center gap-3 cursor-pointer group"
          wire:key="cat-{{ $category['slug'] }}"
        >
          <input
            type="checkbox"
            value="{{ $category['slug'] }}"
            wire:model.live="selectedCategories"
            class="size-5 rounded border-gray-300 text-red-600 focus:ring-red-500"
          >
          <span class="flex-1 group-hover:text-red-600 transition-colors">
            {{ $category['name'] }}
          </span>
          <span class="text-sm text-gray-400">({{ $category['count'] }})</span>
        </label>
      @endforeach
    </div>
  </div>
@else
  <p class="text-sm text-gray-500">{{ __('Geen categorieën beschikbaar.', 'sage') }}</p>
@endif

{{-- Clear Filters Button --}}
@if($this->hasActiveFilters)
  <div class="mt-4 pt-4 border-t border-gray-200">
    <button
      type="button"
      wire:click="clearFilters"
      class="text-sm text-gray-600 hover:text-red-600 underline"
    >
      {{ __('Filters wissen', 'sage') }}
    </button>
  </div>
@endif
