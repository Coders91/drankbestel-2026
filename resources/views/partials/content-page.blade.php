@php(the_content())

@if (isset($pagination) && $pagination)
  <nav class="page-nav" aria-label="Page">
    {!! $pagination !!}
  </nav>
@endif
