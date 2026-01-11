<x-layouts.app>
  <div class="archive-article">
    {{-- Hero Section --}}
    <header class="bg-gray-900 text-white py-12 lg:py-16">
      <div class="container">
        @if($hubCategory)
          {{-- Hub Page Header --}}
          <div class="max-w-4xl">
            <span class="text-primary font-medium mb-2 block">Sterke Drank</span>
            <h1 class="display-1 text-white mb-4">{{ $hubCategory['name'] }}</h1>
            @if($hubCategory['description'])
              <p class="text-lg text-gray-300">{{ $hubCategory['description'] }}</p>
            @endif
          </div>
        @else
          {{-- Generic Archive Header --}}
          <div class="max-w-4xl">
            <h1 class="display-1 text-white mb-4">Artikelen</h1>
            <p class="text-lg text-gray-300">Ontdek alles over sterke drank, cocktails en meer.</p>
          </div>
        @endif
      </div>
    </header>

    {{-- Hub Intro --}}
    @if($hubIntro)
      <section class="container py-12 lg:py-16">
        <div class="max-w-4xl mx-auto prose prose-lg">
          {!! $hubIntro !!}
        </div>
      </section>
    @endif

    {{-- Featured Lists (Top 10's, Best of, etc.) --}}
    @if(count($featuredLists) > 0)
      <x-section.section theme="gray" title="Populaire lijsten">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($featuredLists as $article)
            <x-article-card :article="$article" variant="list" />
          @endforeach
        </div>
      </x-section.section>
    @endif

    {{-- Featured Products --}}
    @if(count($featuredProducts) > 0)
      <x-section.section title="Uitgelichte producten">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
          @foreach($featuredProducts as $product)
            <x-woocommerce.product :product="$product" />
          @endforeach
        </div>

        @if($hubCategory)
          <div class="text-center mt-8">
            <a href="{{ $hubCategory['url'] }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary-dark transition-colors">
              Bekijk alle {{ strtolower($hubCategory['name']) }}
              @svg('icons.arrow-right', 'w-4 h-4')
            </a>
          </div>
        @endif
      </x-section.section>
    @endif

    {{-- Related Cocktails --}}
    @if(count($relatedCocktails) > 0)
      <x-section.section theme="lightgray" title="Cocktails met {{ $hubCategory['name'] ?? 'deze drank' }}">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($relatedCocktails as $cocktail)
            <x-cocktail-card :cocktail="$cocktail" />
          @endforeach
        </div>
      </x-section.section>
    @endif

    {{-- All Articles --}}
    @if(count($articles) > 0)
      <x-section.section title="Alle artikelen" :description="$totalArticles . ' artikelen gevonden'">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($articles as $article)
            <x-article-card :article="$article" />
          @endforeach
        </div>

        @if($pagination)
          <div class="mt-10">
            {!! $pagination !!}
          </div>
        @endif
      </x-section.section>
    @elseif(!$hubCategory)
      <div class="container py-16 text-center">
        <p class="text-gray-500 text-lg">Geen artikelen gevonden.</p>
      </div>
    @endif

    {{-- Featured Articles (curated selection) --}}
    @if(count($featuredArticles) > 0 && count($articles) > 0)
      <x-section.section theme="gray" title="Aanbevolen artikelen">
        <div class="grid md:grid-cols-2 gap-6">
          @foreach($featuredArticles as $article)
            <x-article-card :article="$article" variant="featured" />
          @endforeach
        </div>
      </x-section.section>
    @endif

    {{-- FAQ Section --}}
    @if(count($hubFaq) > 0)
      <x-section.section theme="lightgray" title="Veelgestelde vragen">
        <div class="max-w-3xl mx-auto">
          <x-accordion-group>
            @foreach($hubFaq as $item)
              <x-accordion :title="$item['question']">
                <div class="prose prose-sm">
                  {!! $item['answer'] !!}
                </div>
              </x-accordion>
            @endforeach
          </x-accordion-group>
        </div>
      </x-section.section>
    @endif
  </div>
</x-layouts.app>
