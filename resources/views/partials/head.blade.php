<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php(do_action('get_header'))
  @php(wp_head())
  @livewireStyles
  @vite(['resources/css/app.css'])
  <style id="swiper">{{ Vite::content('resources/css/lib/swiper-bundle.min.css') }}</style>
</head>
