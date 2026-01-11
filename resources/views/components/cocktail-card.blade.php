@props([
  'cocktail' => null,
  'variant' => 'default', // default, compact
])

@if($cocktail)
  <article {{ $attributes->merge(['class' => 'cocktail-card group']) }}>
    <a href="{{ $cocktail['url'] }}" class="block bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
      {{-- Image --}}
      <div class="relative aspect-square overflow-hidden">
        @if($cocktail['imageId'])
          {!! wp_get_attachment_image($cocktail['imageId'], 'medium', false, ['class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300']) !!}
        @else
          <div class="w-full h-full bg-gray-900 flex items-center justify-center">
            @svg('icons.award', 'w-12 h-12 text-gray-700')
          </div>
        @endif

        {{-- Difficulty Badge --}}
        @if($cocktail['difficulty'] ?? false)
          <div class="absolute top-3 right-3">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
              @switch($cocktail['difficulty'])
                @case('easy')
                  bg-green-500/90 text-white
                  @break
                @case('medium')
                  bg-amber-500/90 text-white
                  @break
                @case('hard')
                  bg-red-500/90 text-white
                  @break
                @default
                  bg-gray-500/90 text-white
              @endswitch
            ">
              @switch($cocktail['difficulty'])
                @case('easy')
                  Makkelijk
                  @break
                @case('medium')
                  Gemiddeld
                  @break
                @case('hard')
                  Moeilijk
                  @break
              @endswitch
            </span>
          </div>
        @endif

        {{-- Liquor Type Badges --}}
        @if(count($cocktail['liquorTypes'] ?? []) > 0)
          <div class="absolute bottom-3 left-3 flex flex-wrap gap-1">
            @foreach(array_slice($cocktail['liquorTypes'], 0, 2) as $type)
              <span class="px-2 py-0.5 bg-black/70 text-white text-xs rounded-full">
                {{ $type }}
              </span>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Content --}}
      <div class="p-4">
        <h3 class="font-bold text-lg mb-2 group-hover:text-primary transition-colors line-clamp-1">
          {{ $cocktail['title'] }}
        </h3>

        @if($variant !== 'compact')
          <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $cocktail['excerpt'] }}</p>
        @endif

        {{-- Meta Info --}}
        <div class="flex items-center gap-4 text-xs text-gray-500">
          @if($cocktail['prepTime'] ?? false)
            <span class="flex items-center gap-1">
              @svg('icons.clock', 'w-3.5 h-3.5')
              {{ $cocktail['prepTime'] }} min
            </span>
          @endif

          @if(count($cocktail['cocktailTypes'] ?? []) > 0)
            <span class="flex items-center gap-1">
              @svg('icons.tag-01', 'w-3.5 h-3.5')
              {{ $cocktail['cocktailTypes'][0] }}
            </span>
          @endif
        </div>
      </div>
    </a>
  </article>
@endif
