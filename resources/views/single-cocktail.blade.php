<x-layouts.app>
  <article class="single-cocktail">
    {{-- Hero Section --}}
    <header class="bg-gray-900 text-white py-12 lg:py-16">
      <div class="container">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
          {{-- Image --}}
          <div class="order-2 lg:order-1">
            @if(has_post_thumbnail($cocktail))
              <figure class="rounded-xl overflow-hidden aspect-square max-w-md mx-auto">
                {!! get_the_post_thumbnail($cocktail, 'large', ['class' => 'w-full h-full object-cover']) !!}
              </figure>
            @else
              <div class="aspect-square max-w-md mx-auto bg-gray-800 rounded-xl flex items-center justify-center">
                @svg('icons.award', 'w-24 h-24 text-gray-600')
              </div>
            @endif
          </div>

          {{-- Info --}}
          <div class="order-1 lg:order-2">
            {{-- Category Badges --}}
            <div class="flex flex-wrap gap-2 mb-4">
              @foreach($liquorTypes as $type)
                <a href="{{ $type['url'] }}"
                   class="inline-flex items-center px-3 py-1 bg-white/10 rounded-full text-sm font-medium hover:bg-white/20 transition-colors">
                  {{ $type['name'] }}
                </a>
              @endforeach
              @foreach($cocktailTypes as $type)
                <span class="inline-flex items-center px-3 py-1 bg-primary/20 text-primary-light rounded-full text-sm font-medium">
                  {{ $type['name'] }}
                </span>
              @endforeach
            </div>

            {{-- Title --}}
            <h1 class="display-1 text-white mb-4">{{ $cocktail->post_title }}</h1>

            {{-- Excerpt --}}
            @if($cocktail->post_excerpt)
              <p class="text-lg text-gray-300 mb-6">{{ $cocktail->post_excerpt }}</p>
            @endif

            {{-- Meta Info --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
              @if($prepTime)
                <div class="text-center p-4 bg-white/5 rounded-lg">
                  @svg('icons.clock', 'w-6 h-6 mx-auto mb-2 text-primary')
                  <span class="text-sm text-gray-400">Bereidingstijd</span>
                  <p class="font-semibold">{{ $prepTime }} min</p>
                </div>
              @endif

              @if($servings)
                <div class="text-center p-4 bg-white/5 rounded-lg">
                  @svg('icons.users', 'w-6 h-6 mx-auto mb-2 text-primary')
                  <span class="text-sm text-gray-400">Porties</span>
                  <p class="font-semibold">{{ $servings }}</p>
                </div>
              @endif

              @if($difficulty)
                <div class="text-center p-4 bg-white/5 rounded-lg">
                  @svg('icons.bar-chart', 'w-6 h-6 mx-auto mb-2 text-primary')
                  <span class="text-sm text-gray-400">Moeilijkheid</span>
                  <p class="font-semibold capitalize">
                    @switch($difficulty)
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
                  </p>
                </div>
              @endif

              @if($glassType)
                <div class="text-center p-4 bg-white/5 rounded-lg">
                  @svg('icons.award', 'w-6 h-6 mx-auto mb-2 text-primary')
                  <span class="text-sm text-gray-400">Glas</span>
                  <p class="font-semibold">{{ $glassType }}</p>
                </div>
              @endif
            </div>

            {{-- Brand Association --}}
            @if($brandAssociation)
              <div class="mt-6 pt-6 border-t border-white/10">
                <a href="{{ $brandAssociation['url'] }}"
                   class="inline-flex items-center gap-3 hover:opacity-80 transition-opacity">
                  @if($brandAssociation['thumbnail_url'])
                    <img src="{{ $brandAssociation['thumbnail_url'] }}"
                         alt="{{ $brandAssociation['name'] }}"
                         class="h-10 w-auto bg-white rounded p-1" />
                  @endif
                  <span class="text-sm text-gray-400">Een cocktail van <strong class="text-white">{{ $brandAssociation['name'] }}</strong></span>
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>
    </header>

    {{-- Recipe Content --}}
    <div class="container py-12 lg:py-16">
      <div class="grid lg:grid-cols-3 gap-8 lg:gap-12">
        {{-- Sidebar: Ingredients --}}
        <aside class="lg:col-span-1">
          <div class="sticky top-24 bg-gray-50 rounded-xl p-6">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
              @svg('icons.list', 'w-5 h-5 text-primary')
              Ingredienten
            </h2>

            @if(count($ingredients) > 0)
              <ul class="space-y-3">
                @foreach($ingredients as $ingredient)
                  <li class="flex items-start gap-3">
                    <span class="flex-shrink-0 w-16 text-sm font-medium text-primary">
                      {{ $ingredient['quantity'] }} {{ $ingredient['unit'] }}
                    </span>
                    <span class="flex-1">
                      @if($ingredient['product'])
                        <a href="{{ $ingredient['product']->url }}" class="font-medium hover:text-primary transition-colors">
                          {{ $ingredient['name'] }}
                        </a>
                      @elseif($ingredient['liquor_type'])
                        <a href="{{ $ingredient['liquor_type']['url'] }}" class="hover:text-primary transition-colors">
                          {{ $ingredient['name'] }}
                        </a>
                      @else
                        {{ $ingredient['name'] }}
                      @endif
                    </span>
                  </li>
                @endforeach
              </ul>
            @endif

            @if($garnish)
              <div class="mt-6 pt-4 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-500 mb-2">Garnering</h3>
                <p>{{ $garnish }}</p>
              </div>
            @endif
          </div>
        </aside>

        {{-- Main Content: Instructions --}}
        <div class="lg:col-span-2">
          {{-- Instructions --}}
          @if($instructions)
            <section class="mb-12">
              <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                @svg('icons.clipboard', 'w-6 h-6 text-primary')
                Bereiding
              </h2>

              @if(is_array($instructions))
                <ol class="space-y-6">
                  @foreach($instructions as $index => $step)
                    <li class="flex gap-4">
                      <span class="flex-shrink-0 w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-bold text-sm">
                        {{ $index + 1 }}
                      </span>
                      <div class="flex-1 pt-1 prose">
                        {!! is_array($step) ? ($step['instruction'] ?? $step['text'] ?? '') : $step !!}
                      </div>
                    </li>
                  @endforeach
                </ol>
              @else
                <div class="prose prose-lg max-w-none">
                  {!! $instructions !!}
                </div>
              @endif
            </section>
          @endif

          {{-- Tips --}}
          @if($tips)
            <section class="bg-amber-50 border border-amber-200 rounded-xl p-6">
              <h3 class="text-lg font-bold mb-3 flex items-center gap-2 text-amber-800">
                @svg('icons.zap', 'w-5 h-5')
                Tips van de bartender
              </h3>
              <div class="prose prose-amber">
                {!! nl2br(e($tips)) !!}
              </div>
            </section>
          @endif

          {{-- Main Content --}}
          @if($cocktail->post_content)
            <section class="mt-12 pt-8 border-t border-gray-200">
              <div class="prose prose-lg max-w-none">
                {!! apply_filters('the_content', $cocktail->post_content) !!}
              </div>
            </section>
          @endif
        </div>
      </div>
    </div>

    {{-- Linked Products --}}
    @if(count($linkedProducts) > 0)
      <x-section.section theme="gray" title="Producten voor deze cocktail">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
          @foreach($linkedProducts as $product)
            <x-woocommerce.product :product="$product" />
          @endforeach
        </div>
      </x-section.section>
    @endif
  </article>
</x-layouts.app>
