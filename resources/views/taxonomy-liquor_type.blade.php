<x-layouts.app>
  <div class="taxonomy-liquor-type">
    {{-- Hero Section --}}
    <header class="bg-gray-900 text-white py-12 lg:py-16">
      <div class="container">
        <div class="max-w-4xl">
          <span class="text-primary font-medium mb-2 block">Cocktails</span>
          <h1 class="display-1 text-white mb-4">{{ $liquorType['name'] ?? 'Cocktails' }} Cocktails</h1>
          @if($liquorType['description'] ?? false)
            <p class="text-lg text-gray-300 mb-6">{{ $liquorType['description'] }}</p>
          @else
            <p class="text-lg text-gray-300 mb-6">Ontdek de beste cocktails met {{ strtolower($liquorType['name'] ?? 'deze drank') }}.</p>
          @endif

          <div class="flex items-center gap-4 text-sm text-gray-400">
            <span class="flex items-center gap-2">
              @svg('icons.award', 'w-4 h-4')
              {{ $totalCocktails }} {{ $totalCocktails === 1 ? 'cocktail' : 'cocktails' }}
            </span>
          </div>
        </div>
      </div>
    </header>

    {{-- Filter by Cocktail Type --}}
    @if(count($cocktailTypes) > 0)
      <div class="border-b border-gray-200">
        <div class="container py-4">
          <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-gray-500 mr-2">Filter:</span>
            <a href="{{ $liquorType['url'] }}"
               class="px-3 py-1 text-sm rounded-full transition-colors
                 {{ !request()->query('cocktail_type') ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
              Alle
            </a>
            @foreach($cocktailTypes as $type)
              <a href="{{ add_query_arg('cocktail_type', $type['slug'], $liquorType['url']) }}"
                 class="px-3 py-1 text-sm rounded-full transition-colors
                   {{ request()->query('cocktail_type') === $type['slug'] ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                {{ $type['name'] }} ({{ $type['count'] }})
              </a>
            @endforeach
          </div>
        </div>
      </div>
    @endif

    {{-- Cocktails Grid --}}
    <div class="container py-12 lg:py-16">
      @if(count($cocktails) > 0)
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($cocktails as $cocktail)
            <x-cocktail-card :cocktail="$cocktail" />
          @endforeach
        </div>

        @if($pagination)
          <div class="mt-10">
            {!! $pagination !!}
          </div>
        @endif
      @else
        <div class="text-center py-16">
          @svg('icons.award', 'w-16 h-16 mx-auto text-gray-300 mb-4')
          <p class="text-gray-500 text-lg">Geen cocktails gevonden.</p>
          <a href="/cocktails/" class="mt-4 inline-flex items-center gap-2 text-primary hover:underline">
            @svg('icons.arrow-left', 'w-4 h-4')
            Bekijk alle cocktails
          </a>
        </div>
      @endif
    </div>

    {{-- Related Liquor Types --}}
    @php
      $otherLiquorTypes = get_terms([
        'taxonomy' => 'liquor_type',
        'hide_empty' => true,
        'exclude' => [$liquorType['id'] ?? 0],
        'number' => 6,
      ]);
    @endphp

    @if(!is_wp_error($otherLiquorTypes) && count($otherLiquorTypes) > 0)
      <x-section.section theme="gray" title="Andere dranken">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          @foreach($otherLiquorTypes as $type)
            <a href="{{ get_term_link($type) }}"
               class="flex flex-col items-center justify-center p-6 bg-white rounded-xl hover:shadow-lg transition-shadow text-center">
              <span class="text-lg font-bold mb-1">{{ $type->name }}</span>
              <span class="text-sm text-gray-500">{{ $type->count }} cocktails</span>
            </a>
          @endforeach
        </div>
      </x-section.section>
    @endif
  </div>
</x-layouts.app>
