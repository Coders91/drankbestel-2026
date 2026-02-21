{{--
  Template Name: Custom Template
--}}

<x-layouts.app>
  @while(have_posts()) @php(the_post())
    @include('partials.page-header')
    @include('partials.content-page')
  @endwhile
</x-layouts.app>
