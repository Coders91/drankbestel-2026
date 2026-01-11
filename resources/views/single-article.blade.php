<x-layouts.app>
  <article class="single-article">
    {{-- Hero Section --}}
    <header class="bg-gray-50 py-12 lg:py-16">
      <div class="container">
        <div class="max-w-4xl mx-auto">
          {{-- Category Badge --}}
          @if($primaryCategory)
            <a href="{{ $primaryCategory->url }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline mb-4">
              {{ $primaryCategory->name }}
            </a>
          @endif

          {{-- Title --}}
          <h1 class="display-1 mb-6">{{ $article->post_title }}</h1>

          {{-- Excerpt --}}
          @if($article->post_excerpt)
            <p class="text-lg text-gray-600 mb-6">{{ $article->post_excerpt }}</p>
          @endif

          {{-- Meta Info --}}
          <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
            @if($article->post_author)
              <span class="flex items-center gap-2">
                @svg('icons.user', 'w-4 h-4')
                {{ get_the_author_meta('display_name', $article->post_author) }}
              </span>
            @endif

            <span class="flex items-center gap-2">
              @svg('icons.calendar', 'w-4 h-4')
              <time datetime="{{ get_the_date('c', $article) }}">
                {{ get_the_date('j F Y', $article) }}
              </time>
            </span>

            @if($contentFormat === 'list' && $lastUpdated)
              <span class="flex items-center gap-2 text-primary">
                @svg('icons.refresh-cw', 'w-4 h-4')
                Bijgewerkt: {{ $lastUpdated }}
              </span>
            @endif
          </div>
        </div>
      </div>
    </header>

    {{-- Featured Image --}}
    @if(has_post_thumbnail($article))
      <div class="container py-8">
        <div class="max-w-4xl mx-auto">
          <figure class="rounded-xl overflow-hidden aspect-video">
            {!! get_the_post_thumbnail($article, 'large', ['class' => 'w-full h-full object-cover']) !!}
          </figure>
        </div>
      </div>
    @endif

    {{-- Content Section --}}
    <div class="container pb-16 lg:pb-24">
      <div class="max-w-4xl mx-auto">
        @if($contentFormat === 'list')
          @include('partials.article-list')
        @else
          @include('partials.article-standard')
        @endif
      </div>
    </div>

    {{-- Related Products --}}
    @if(count($relatedProducts) > 0)
      <x-section.section theme="gray" title="Gerelateerde producten">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
          @foreach($relatedProducts as $product)
            <x-woocommerce.product :product="$product" />
          @endforeach
        </div>
      </x-section.section>
    @endif

    {{-- Related Brands --}}
    @if(count($relatedBrands) > 0)
      <x-section.section theme="lightgray" title="Bekijk ook deze merken">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
          @foreach($relatedBrands as $brand)
            <a href="{{ $brand['url'] }}"
               class="flex flex-col items-center justify-center p-4 bg-white rounded-lg hover:shadow-md transition-shadow">
              @if($brand['thumbnail_url'])
                <img src="{{ $brand['thumbnail_url'] }}"
                     alt="{{ $brand['name'] }}"
                     class="h-12 w-auto object-contain mb-2" />
              @endif
              <span class="text-sm font-medium text-center">{{ $brand['name'] }}</span>
            </a>
          @endforeach
        </div>
      </x-section.section>
    @endif
  </article>
</x-layouts.app>
