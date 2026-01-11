<x-app>
  @while(have_posts()) @php(the_post())
  @includeFirst(['partials.content-page', 'partials.content'])
  @endwhile
</x-app>
