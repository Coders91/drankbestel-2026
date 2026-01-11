@props([
  'article' => null,
  'variant' => 'default', // default, list, featured
])

@if($article)
  <article {{ $attributes->merge(['class' => 'article-card group']) }}>
    @if($variant === 'featured')
      {{-- Featured Variant: Horizontal card --}}
      <a href="{{ $article['url'] }}" class="flex flex-col sm:flex-row gap-4 bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
        {{-- Image --}}
        <div class="sm:w-1/3 aspect-video sm:aspect-square overflow-hidden flex-shrink-0">
          @if($article['imageId'])
            {!! wp_get_attachment_image($article['imageId'], 'medium', false, ['class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300']) !!}
          @else
            <div class="w-full h-full bg-gray-100 flex items-center justify-center">
              @svg('icons.file-text', 'w-8 h-8 text-gray-300')
            </div>
          @endif
        </div>

        {{-- Content --}}
        <div class="flex-1 p-4 sm:py-4 sm:pr-4 flex flex-col">
          @if($article['primaryCategory'])
            <span class="text-xs font-medium text-primary mb-2">{{ $article['primaryCategory']['name'] }}</span>
          @endif
          <h3 class="font-bold text-lg mb-2 group-hover:text-primary transition-colors line-clamp-2">{{ $article['title'] }}</h3>
          <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $article['excerpt'] }}</p>
          <div class="mt-auto flex items-center justify-between text-xs text-gray-500">
            <span>{{ $article['date'] }}</span>
            @if($article['contentFormat'] === 'list')
              <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full">
                {{ ucfirst($article['listVariant'] ?? 'Top') }} lijst
              </span>
            @endif
          </div>
        </div>
      </a>

    @elseif($variant === 'list')
      {{-- List Variant: Emphasized list badge --}}
      <a href="{{ $article['url'] }}" class="block bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
        {{-- Image with Badge --}}
        <div class="relative aspect-video overflow-hidden">
          @if($article['imageId'])
            {!! wp_get_attachment_image($article['imageId'], 'medium', false, ['class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300']) !!}
          @else
            <div class="w-full h-full bg-gray-100 flex items-center justify-center">
              @svg('icons.file-text', 'w-8 h-8 text-gray-300')
            </div>
          @endif

          {{-- List Badge --}}
          <div class="absolute top-3 left-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
              @switch($article['listVariant'] ?? '')
                @case('best')
                  bg-amber-500 text-white
                  @break
                @case('cheapest')
                  bg-green-500 text-white
                  @break
                @case('seasonal')
                  bg-blue-500 text-white
                  @break
                @default
                  bg-primary text-white
              @endswitch
            ">
              @switch($article['listVariant'] ?? '')
                @case('best')
                  Top keuze
                  @break
                @case('cheapest')
                  Budget tips
                  @break
                @case('seasonal')
                  Seizoen
                  @break
                @default
                  Top lijst
              @endswitch
            </span>
          </div>
        </div>

        {{-- Content --}}
        <div class="p-4">
          @if($article['primaryCategory'])
            <span class="text-xs font-medium text-primary mb-1 block">{{ $article['primaryCategory']['name'] }}</span>
          @endif
          <h3 class="font-bold text-lg mb-2 group-hover:text-primary transition-colors line-clamp-2">{{ $article['title'] }}</h3>
          <p class="text-sm text-gray-600 line-clamp-2">{{ $article['excerpt'] }}</p>
        </div>
      </a>

    @else
      {{-- Default Variant: Simple card --}}
      <a href="{{ $article['url'] }}" class="block bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
        {{-- Image --}}
        <div class="aspect-video overflow-hidden">
          @if($article['imageId'])
            {!! wp_get_attachment_image($article['imageId'], 'medium', false, ['class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300']) !!}
          @else
            <div class="w-full h-full bg-gray-100 flex items-center justify-center">
              @svg('icons.file-text', 'w-8 h-8 text-gray-300')
            </div>
          @endif
        </div>

        {{-- Content --}}
        <div class="p-4">
          <div class="flex items-center gap-2 mb-2">
            @if($article['primaryCategory'])
              <span class="text-xs font-medium text-primary">{{ $article['primaryCategory']['name'] }}</span>
              <span class="text-gray-300">|</span>
            @endif
            <span class="text-xs text-gray-500">{{ $article['date'] }}</span>
          </div>
          <h3 class="font-bold mb-2 group-hover:text-primary transition-colors line-clamp-2">{{ $article['title'] }}</h3>
          <p class="text-sm text-gray-600 line-clamp-2">{{ $article['excerpt'] }}</p>

          @if($article['contentFormat'] === 'list')
            <div class="mt-3 pt-3 border-t border-gray-100">
              <span class="text-xs font-medium text-amber-600">
                @switch($article['listVariant'] ?? '')
                  @case('best')
                    Beste keuze selectie
                    @break
                  @case('cheapest')
                    Budget selectie
                    @break
                  @case('seasonal')
                    Seizoen selectie
                    @break
                  @default
                    Top selectie
                @endswitch
              </span>
            </div>
          @endif
        </div>
      </a>
    @endif
  </article>
@endif
