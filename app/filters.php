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

/**
 * Fix "doing it wrong" error from wp_enqueue_script() on widget screen (Sage theme related)
 *
 */
remove_filter('admin_head', 'wp_check_widget_editor_deps');


/**
 * Prevent per block global css from being added. Since WordPress 6.9
 */
add_filter( 'should_load_separate_core_block_assets', '__return_false' );


/**
 * Set out of stock products last
 */
add_filter('posts_clauses', function($clauses, $query) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_tax( get_object_taxonomies('product') ) ) {
        return $clauses;
    }

    global $wpdb;

    // Check if the join already exists to avoid adding it multiple times
    if (!str_contains($clauses['join'], "LEFT JOIN {$wpdb->posts} AS stock_meta")) {
        $clauses['join'] .= "
            LEFT JOIN {$wpdb->postmeta} AS stock_meta
            ON ({$wpdb->posts}.ID = stock_meta.post_id
            AND stock_meta.meta_key = '_stock_status')
        ";
    }

    // Check if stock order already exists before adding
    if (!str_contains($clauses['orderby'], "stock_meta.meta_value")) {
        // Prepend stock status order: 'instock' comes before 'outofstock' alphabetically
        $clauses['orderby'] = "stock_meta.meta_value ASC, " . $clauses['orderby'];
    }

    return $clauses;
}, 10, 2);

/**
 * Remove <p> tag from archive descriptions
 *
 */
remove_filter('term_description','wpautop');

// Disable WooCommerce scripts at a lower level
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// Remove inline styles that WooCommerce adds
add_filter('woocommerce_inline_styles', '__return_empty_string');

add_filter( 'woocommerce_structured_data_product', function( $markup, $product ){
    if( is_product() ) {
        $markup = [];
    }
    return $markup;
}, 10, 2 );

add_filter( 'wpseo_json_ld_output', '__return_false' );

/**
 * Dynamically populate proposed_filters field choices from FilterEverything
 */
add_filter('acf/prepare_field/name=proposed_filters', function ($field) {
    global $pagenow;

    if ($pagenow !== 'term.php' || !is_admin()) {
        return $field;
    }

    $taxonomy = sanitize_text_field($_GET['taxonomy'] ?? '');
    $termId = intval($_GET['tag_ID'] ?? 0);

    if (!$termId || !in_array($taxonomy, ['product_cat', 'product_brand'])) {
        return $field;
    }

    $service = new \App\Services\FilterEverythingService();
    $field['choices'] = $service->getProposedFilterChoices($termId, $taxonomy);

    return $field;
});

/**
 * Replace %primary_cat% placeholder in article permalinks
 */
add_filter('post_type_link', function ($permalink, $post) {
    if ($post->post_type !== 'article') {
        return $permalink;
    }

    $primaryCat = get_field('primary_category', $post->ID);

    if ($primaryCat) {
        $term = is_object($primaryCat) ? $primaryCat : get_term($primaryCat, 'product_cat');

        if ($term && ! is_wp_error($term)) {
            return str_replace('%primary_cat%', $term->slug, $permalink);
        }
    }

    // Fallback: uncategorized
    return str_replace('%primary_cat%', 'uncategorized', $permalink);
}, 10, 2);

/**
 * Modify query for article hub pages
 */
add_action('pre_get_posts', function ($query) {
    if (is_admin() || ! $query->is_main_query()) {
        return;
    }

    $hubSlug = $query->get('article_hub');

    if (! $hubSlug) {
        return;
    }

    $term = get_term_by('slug', $hubSlug, 'product_cat');

    if ($term) {
        $query->set('post_type', 'article');
        $query->set('meta_query', [[
            'key' => 'primary_category',
            'value' => $term->term_id,
            'compare' => '=',
        ]]);
    }
});

