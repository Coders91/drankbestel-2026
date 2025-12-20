<!doctype html>
@props([
    'header' => true,
    'hero' => false,
    'breadcrumbs' => true,
    'main' => true,
    'sidebar' => false,
    'footer' => true,
])

<html @php(language_attributes()) class="h-full">

@include('partials.head')

<body @php(body_class('h-full'))>
@php(wp_body_open())

<div
  id="app"
  class="relative flex flex-col h-full"
  x-data="{
    favorites: [],
    initFavorites() {
      try {
        this.favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
      } catch {
        this.favorites = [];
      }
    },
    isFavorite(productId) {
      return this.favorites.includes(Number(productId));
    },
    toggleFavorite(productId) {
      const id = Number(productId);
      if (this.isFavorite(id)) {
        this.favorites = this.favorites.filter(fav => fav !== id);
      } else {
        this.favorites.push(id);
      }
      localStorage.setItem('favorites', JSON.stringify(this.favorites));
    }
  }"
  x-init="initFavorites(); window.addEventListener('storage', () => initFavorites())"
>
  <a class="sr-only focus:not-sr-only" href="#site-content">
    {{ __('Skip to content', 'sage') }}
  </a>

  {{-- Header Section --}}
  @if(isset($header) && !is_bool($header))
    <header {{ $header->attributes->class(['site-header']) }}>
      {{ $header }}
    </header>
  @else
    @includeWhen($header !== false, 'sections.header')
  @endif

  {{-- Main content Section --}}
  <main id="site-content" {{ $attributes->merge(['class' => 'site-content flex-1 flex flex-col']) }}>
    {{-- Hero --}}
    @if(isset($hero) && !is_bool($hero))
      <section {{ $hero->attributes->class(['hero']) }}>
        {{ $hero }}
      </section>
    @else
      @includeWhen($hero !== false, 'partials.hero')
    @endif

    {{-- Breadcrumbs --}}
    @if($breadcrumbs)
      <x-breadcrumbs />
    @endif

    <div
      class="z-[0] flex {{ $sidebar ? 'container flex-row justify-between gap-y-8 gap-x-12 max-lg:flex-col mx-auto px-4 lg:px-8' : 'flex-col' }}">
      {{-- Main --}}
      <article class="w-full {{ $sidebar ? 'lg:max-w-screen-md' : 'flex-grow' }}">
        @if(isset($main) && !is_bool($main))
          {{ $main }}
        @else
          {{ $slot }}
        @endif
      </article>

      {{-- Sidebar --}}
      @if(isset($sidebar) && is_string($sidebar))
        <aside class="lg:max-w-screen-sm">
          @include('sections.sidebar')
        </aside>
      @elseif($sidebar !== false)
        <aside {{ $sidebar->attributes->class(['lg:max-w-screen-sm']) }}>
          {{ $sidebar }}
        </aside>
      @endif
    </div>
  </main>

  {{-- Footer Section --}}
  @if(isset($footer) && !is_bool($footer))
    <footer {{ $footer->getAttributes() }}>
      {{ $footer }}
    </footer>
  @else
    @includeWhen($footer !== false, 'sections.footer')
  @endif

  {{-- Mini Cart Toast --}}
  <livewire:mini-cart />
</div>

@php(do_action('get_footer'))
@php(wp_footer())

@livewireScripts
@vite('resources/js/app.js')
@stack('scripts')
</body>
</html>
