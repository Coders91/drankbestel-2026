<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_filter('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    $dependencies = json_decode(Vite::content('editor.deps.json'));

    foreach ($dependencies as $dependency) {
        if (! wp_script_is($dependency)) {
            wp_enqueue_script($dependency);
        }
    }

    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
        'mobile_navigation' => __('Mobile Navigation', 'sage'),
        'footer_navigation' => __('Footer Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

add_action( 'woocommerce_admin_order_data_after_order_details', function( $order ) {
    echo '<a href="' . $order->get_checkout_order_received_url() . '" target="_blank" class="button" style="margin-top:16px">' . __( 'Bekijk bedankpagina', 'woocommerce' ) . '</a>';
});

/**
 * Remove unwanted assets from WordPress & plugins
 *
 */
add_action('wp_enqueue_scripts', function() {
    if (is_admin()) return;

    // Remove WooCommerce styles
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_style('woocommerce_frontend_styles');
    wp_dequeue_style('woocommerce_fancybox_styles');
    wp_dequeue_style('woocommerce_chosen_styles');
    wp_dequeue_style('woocommerce-inline');
    wp_dequeue_style('brands-styles');
    wp_dequeue_style('wc-blocks-style');
    wp_dequeue_style('global-styles');

    // Remove WooCommerce scripts
    wp_dequeue_script('woocommerce');
    wp_dequeue_script('woocommerce-inline');
    wp_dequeue_script('wc-cart-fragments');
    wp_dequeue_script('wc-add-to-cart');
    wp_dequeue_script('wc-checkout');
    wp_dequeue_script('wc-add-to-cart-variation');
    wp_dequeue_script('wc-single-product');
    wp_dequeue_script('wc-cart');
    wp_dequeue_script('wc-price-slider');
    wp_dequeue_script('wc-chosen');
    wp_dequeue_script('jquery-blockui');
    wp_dequeue_script('jquery-placeholder');
    wp_dequeue_script('jquery-payment');
    wp_dequeue_script('fancybox');
    wp_dequeue_script('jqueryui');
}, 99);

// Remove global styles
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

// Clear the entire WP script queue right before output so no plugin scripts (including jQuery dependents) ever print
add_action('wp_print_scripts', function () {
    if (is_admin()) return;
    global $wp_scripts;
    $wp_scripts->queue = [];
}, PHP_INT_MAX);

// Remove jQuery
add_action('wp_enqueue_scripts', function() {
    if (is_admin()) return;
    wp_dequeue_script('jquery');
    wp_deregister_script('jquery');
    wp_dequeue_script('jquery-migrate');
    wp_deregister_script('jquery-migrate');
    wp_dequeue_script('jquery-core');
    wp_deregister_script('jquery-core');
    wp_deregister_script('jquery-ui-core');
    wp_dequeue_script('jquery-ui-core');
    wp_deregister_script('jquery-ui-mouse');
    wp_dequeue_script('jquery-ui-mouse');
    wp_deregister_script('jquery-ui-menu');
    wp_dequeue_script('jquery-ui-menu');
    wp_deregister_script('jquery-ui-button');
    wp_dequeue_script('jquery-ui-button');
    wp_deregister_script('jquery-ui-draggable');
    wp_dequeue_script('jquery-ui-draggable');
    wp_deregister_script('jquery-ui-slider');
    wp_dequeue_script('jquery-ui-slider');
    wp_deregister_script('customize-base');
    wp_dequeue_script('customize-base');
}, 99);

// Filtereverything plugin
add_action( 'wp_print_styles', function () {
    wp_dequeue_style( 'wpc-filter-everything' );
    wp_deregister_style( 'wpc-filter-everything' );

    wp_dequeue_style( 'wpc-widgets' );
    wp_deregister_style( 'wpc-widgets' );
}, 999 );

add_action('wp_print_scripts', function () {
    wp_deregister_script('wpc-filter-everything');
    wp_dequeue_script('wpc-filter-everything');
}, 999);

// Remove block assets
add_action('init', function() {
    if (is_admin()) return;
    $blockStyles = [
        'wp-block-library',
        'wc-blocks-style',
        'wc-blocks-style-active-filters',
        'wc-blocks-style-add-to-cart-form',
        'wc-blocks-packages-style',
        'wc-blocks-style-all-products',
        'wc-blocks-style-all-reviews',
        'wc-blocks-style-attribute-filter',
        'wc-blocks-style-breadcrumbs',
        'wc-blocks-style-catalog-sorting',
        'wc-blocks-style-customer-account',
        'wc-blocks-style-featured-category',
        'wc-blocks-style-featured-product',
        'wc-blocks-style-mini-cart',
        'wc-blocks-style-price-filter',
        'wc-blocks-style-product-add-to-cart',
        'wc-blocks-style-product-button',
        'wc-blocks-style-product-categories',
        'wc-blocks-style-product-image',
        'wc-blocks-style-product-image-gallery',
        'wc-blocks-style-product-query',
        'wc-blocks-style-product-results-count',
        'wc-blocks-style-product-reviews',
        'wc-blocks-style-product-sale-badge',
        'wc-blocks-style-product-search',
        'wc-blocks-style-product-sku',
        'wc-blocks-style-product-stock-indicator',
        'wc-blocks-style-product-summary',
        'wc-blocks-style-product-title',
        'wc-blocks-style-rating-filter',
        'wc-blocks-style-reviews-by-category',
        'wc-blocks-style-reviews-by-product',
        'wc-blocks-style-product-details',
        'wc-blocks-style-single-product',
        'wc-blocks-style-stock-filter',
        'wc-blocks-style-cart',
        'wc-blocks-style-checkout',
        'wc-blocks-style-mini-cart-contents',
        'classic-theme-styles-inline'
    ];

    foreach ( $blockStyles as $style ) {
        wp_deregister_style( $style );
    }

}, 99);
