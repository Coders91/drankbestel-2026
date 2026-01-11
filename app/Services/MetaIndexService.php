<?php

namespace App\Services;

class MetaIndexService
{
    /**
     * Update the flattened product index for a cocktail.
     * Extracts linked_product IDs from the liquors repeater and stores them
     * in a pipe-delimited format for efficient LIKE queries.
     *
     * Also syncs liquor_type terms from the repeater rows.
     */
    public function updateCocktailProductIndex(int $postId): void
    {
        $liquors = get_field('liquors', $postId) ?: [];

        // Extract product IDs from linked_product field
        $productIds = [];
        $liquorTypeIds = [];

        foreach ($liquors as $liquor) {
            if (! empty($liquor['linked_product'])) {
                $productIds[] = (int) $liquor['linked_product'];
            }

            if (! empty($liquor['liquor_type'])) {
                $liquorTypeIds[] = (int) $liquor['liquor_type'];
            }
        }

        // Store as pipe-delimited string: |12|48|301|
        $productIds = array_unique(array_filter($productIds));
        $index = $productIds ? '|' . implode('|', $productIds) . '|' : '';
        update_post_meta($postId, '_linked_product_ids_index', $index);

        // Sync liquor_type terms from repeater
        $liquorTypeIds = array_unique(array_filter($liquorTypeIds));
        if ($liquorTypeIds) {
            wp_set_object_terms($postId, $liquorTypeIds, 'liquor_type');
        }
    }

    /**
     * Update the flattened product index for a list-format article.
     * Extracts product IDs from the list_items repeater and stores them
     * in a pipe-delimited format for efficient LIKE queries.
     */
    public function updateArticleProductIndex(int $postId): void
    {
        $contentFormat = get_field('content_format', $postId);

        // Only index list-format articles
        if ($contentFormat !== 'list') {
            delete_post_meta($postId, '_listed_product_ids_index');

            return;
        }

        $listItems = get_field('list_items', $postId) ?: [];

        // Extract product IDs
        $productIds = [];

        foreach ($listItems as $item) {
            if (! empty($item['product'])) {
                $productIds[] = (int) $item['product'];
            }
        }

        // Store as pipe-delimited string: |12|48|301|
        $productIds = array_unique(array_filter($productIds));
        $index = $productIds ? '|' . implode('|', $productIds) . '|' : '';
        update_post_meta($postId, '_listed_product_ids_index', $index);
    }

    /**
     * Get cocktails that link to a specific product.
     *
     * @return \WP_Post[]
     */
    public function getCocktailsForProduct(int $productId): array
    {
        return get_posts([
            'post_type' => 'cocktail',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => '_linked_product_ids_index',
                'value' => '|' . $productId . '|',
                'compare' => 'LIKE',
            ]],
        ]);
    }

    /**
     * Get list-format articles that contain a specific product.
     *
     * @return \WP_Post[]
     */
    public function getListArticlesForProduct(int $productId): array
    {
        return get_posts([
            'post_type' => 'article',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_listed_product_ids_index',
                    'value' => '|' . $productId . '|',
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'content_format',
                    'value' => 'list',
                    'compare' => '=',
                ],
            ],
        ]);
    }

    /**
     * Get articles related to a product (via featured_on_products or related_products).
     *
     * @return \WP_Post[]
     */
    public function getArticlesForProduct(int $productId): array
    {
        // Articles where this product is in featured_on_products
        $featuredArticles = get_posts([
            'post_type' => 'article',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => 'featured_on_products',
                'value' => '"' . $productId . '"',
                'compare' => 'LIKE',
            ]],
        ]);

        // Articles where this product is in related_products
        $relatedArticles = get_posts([
            'post_type' => 'article',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => 'related_products',
                'value' => '"' . $productId . '"',
                'compare' => 'LIKE',
            ]],
        ]);

        // Merge and deduplicate
        $allArticles = array_merge($featuredArticles, $relatedArticles);
        $uniqueIds = [];
        $result = [];

        foreach ($allArticles as $article) {
            if (! isset($uniqueIds[$article->ID])) {
                $uniqueIds[$article->ID] = true;
                $result[] = $article;
            }
        }

        return $result;
    }
}
