<?php

namespace App\Services\Woocommerce;

use WP_Post;

/**
 * Removes the WooCommerce product base using rewrite rules.
 *
 * Example:
 *   /product/my-shoe/  →  /my-shoe/
 */
class ProductBaseRemovalService
{
    public static function register(): void
    {
        add_filter('post_type_link', [self::class, 'filterProductPermalink'], 10, 2);

        add_action('init', [self::class, 'addRewriteRules']);
        add_action('init', [self::class, 'addQueryVars']);

        add_action('template_redirect', [self::class, 'handleCanonicalRedirect']);
    }

    /**
     * Remove product base from generated permalinks.
     */
    public static function filterProductPermalink(string $permalink, WP_Post $post): string
    {
        if ($post->post_type !== 'product') {
            return $permalink;
        }

        if (! get_option('permalink_structure')) {
            return $permalink;
        }

        return home_url('/' . $post->post_name . '/');
    }

    /**
     * Register rewrite rules.
     */
    public static function addRewriteRules(): void
    {
        /*
         * Match:
         *   /product-slug/
         *
         * and map to:
         *   index.php?product=product-slug
         */
        add_rewrite_rule(
            '^([^/]+)/?$',
            'index.php?product=$matches[1]',
            'top'
        );
    }

    /**
     * Allow custom query var.
     */
    public static function addQueryVars(): void
    {
        add_filter('query_vars', function ($vars) {
            $vars[] = 'product';
            return $vars;
        });
    }

    /**
     * Prevent duplicate canonical redirects.
     */
    public static function handleCanonicalRedirect(): void
    {
        if (is_singular('product')) {
            remove_action('template_redirect', 'redirect_canonical');
        }
    }
}
