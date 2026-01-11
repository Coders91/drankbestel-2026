<x-layouts.app>
  <div class="archive-cocktail">
    {{-- Hero Section --}}
    <header class="bg-gray-900 text-white py-12 lg:py-16">
      <div class="container">
        <div class="max-w-4xl">
          <h1 class="display-1 text-white mb-4">Cocktails</h1>
          <p class="text-lg text-gray-300 mb-6">
            Ontdek onze verzameling cocktail recepten. Van klassiekers tot moderne creaties.
          </p>
          <div class="flex items-center gap-4 text-sm text-gray-400">
            <span class="flex items-center gap-2">
              @svg('icons.award', 'w-4 h-4')
              {{ $totalCocktails }} {{ $totalCocktails === 1 ? 'cocktail' : 'cocktails' }}
            </span>
          </div>
        </div>
      </div>
    </header>

    {{-- Filter by Liquor Type --}}
    @if(count($liquorTypes) > 0)
      <div class="bg-gray-50 border-b border-gray-200">
        <div class="container py-6">
          <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Kies op basis van drank</h2>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($liquorTypes as $type)
              <a href="{{ $type['url'] }}"
                 class="flex flex-col items-center justify-center p-4 bg-white rounded-xl hover:shadow-md transition-shadow text-center">
                <span class="font-bold mb-1">{{ $type['name'] }}</span>
                <span class="text-xs text-gray-500">{{ $type['count'] }} cocktails</span>
              </a>
            @endforeach
          </div>
        </div>
      </div>
    @endif

    {{-- Filter by Cocktail Type --}}
    @if(count($cocktailTypes) > 0)
      <div class="border-b border-gray-200">
        <div class="container py-4">
          <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-gray-500 mr-2">Type:</span>
            <a href="/cocktails/"
               class="px-3 py-1 text-sm rounded-full transition-colors
                 {{ !request()->query('type') ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
              Alle
            </a>
            @foreach($cocktailTypes as $type)
              <a href="{{ $type['url'] }}"
                 class="px-3 py-1 text-sm rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
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
        <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
          <p class="text-gray-500 text-lg">Nog geen cocktails beschikbaar.</p>
        </div>
      @endif
    </div>
  </div>
</x-layouts.app>
