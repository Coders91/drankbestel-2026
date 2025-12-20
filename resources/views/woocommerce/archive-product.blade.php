{{--
The Template for displaying product archives, including the main shop page which is a post type archive

This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.

HOWEVER, on occasion WooCommerce will need to update template files and you
(the theme developer) will need to copy the new files to your theme to
maintain compatibility. We try to do this as little as possible, but it does
happen. When this occurs the version of the template file will be bumped and
the readme will list any important changes.

@see https://docs.woocommerce.com/document/template-structure/
@package WooCommerce/Templates
@version 3.4.0
--}}

<x-app>
  @php
    do_action('get_header', 'shop');
    do_action('woocommerce_before_main_content');
  @endphp

  <header class="woocommerce-products-header">
    @if (apply_filters('woocommerce_show_page_title', true))
      <h1 class="woocommerce-products-header__title page-title">{!! woocommerce_page_title(false) !!}</h1>
    @endif

    @php
      do_action('woocommerce_archive_description')
    @endphp
  </header>

    @php
      do_action('woocommerce_before_shop_loop');
      woocommerce_product_loop_start();
    @endphp

  <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
    @if ($products->isNotEmpty())
      @forelse ($products as $product)
        <x-product :product="$product" />
      @empty
        Geen producten gevonden.
      @endforelse
    @endif
  </div>

  @php
    woocommerce_product_loop_end();
  @endphp
    {!! $pagination !!}

  @php
    do_action('woocommerce_after_main_content');
    do_action('get_sidebar', 'shop');
    do_action('get_footer', 'shop');
  @endphp
</x-app>
