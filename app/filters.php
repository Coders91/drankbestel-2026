<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

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
    wp_dequeue_script('wc-cart-fragments');
    wp_dequeue_script('woocommerce');
    wp_dequeue_script('wc-add-to-cart');
    wp_dequeue_script('wc-checkout');
    wp_dequeue_script('wc-add-to-cart-variation');
    wp_dequeue_script('wc-single-product');
    wp_dequeue_script('wc-cart');
    wp_dequeue_script('wc-chosen');
    wp_dequeue_script('woocommerce-inline');
    wp_dequeue_script('jquery-blockui');
    wp_dequeue_script('jquery-placeholder');
    wp_dequeue_script('jquery-payment');
    wp_dequeue_script('fancybox');
    wp_dequeue_script('jqueryui');
}, 99);

// Disable WooCommerce scripts at a lower level
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// Remove inline styles that WooCommerce adds
add_filter('woocommerce_inline_styles', '__return_empty_string');

// Disable cart fragments (AJAX cart updates)
add_action('wp_enqueue_scripts', function() {
    if (is_admin()) return;
    wp_deregister_script('wc-cart-fragments');
}, 99);

add_action('wp_enqueue_scripts', function() {
    if (is_admin()) return;
    wp_dequeue_style('global-styles');
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_script('wc-cart-fragments');
    wp_dequeue_script('woocommerce');
    wp_dequeue_script('jquery');
    wp_deregister_script('jquery');
    wp_dequeue_script('jquery-core');
    wp_deregister_script('jquery-core');
    wp_dequeue_script('jquery-migrate');
    wp_deregister_script('jquery-migrate');
}, 99);

remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

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
