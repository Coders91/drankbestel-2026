{{--
  Template Name: Custom Template
--}}

<x-layouts.app>
  @while(have_posts()) @php(the_post())
  <x-page-header />
  @include('partials.content-page')
  @endwhile
</x-layouts.app>
