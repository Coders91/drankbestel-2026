<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php(do_action('get_header'))
  @php(wp_head())
  @livewireStyles
  @vite(['resources/css/app.css'])
  @isset($structuredData)
    {!! $structuredData !!}
  @endisset
  <script defer src="{{ Vite::asset('resources/js/instant.page.js') }}"></script>
</head>
