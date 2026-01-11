<?php

namespace App\View\Composers;

use App\View\Models\Product;
use Log1x\Pagi\PagiFacade as Pagi;
use Roots\Acorn\View\Composer;

class ArticleHub extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'archive-article',
    ];

    /**
     * Data to be passed to view before rendering.
     */
    public function with(): array
    {
        global $wp_query;

        $hubSlug = get_query_var('article_hub');
        $hubCategory = null;

        if ($hubSlug) {
            $hubCategory = get_term_by('slug', $hubSlug, 'product_cat');
        }

        // If no hub category found, return basic archive data
        if (! $hubCategory || is_wp_error($hubCategory)) {
            return $this->getBasicArchiveData($wp_query);
        }

        return [
            'hubCategory' => $this->formatTerm($hubCategory),
            'hubIntro' => get_field('hub_intro', 'product_cat_' . $hubCategory->term_id),
            'hubFaq' => $this->getFaq($hubCategory->term_id),
            'featuredProducts' => $this->getFeaturedProducts($hubCategory->term_id),
            'featuredArticles' => $this->getFeaturedArticles($hubCategory->term_id),
            'featuredLists' => $this->getFeaturedLists($hubCategory->term_id),
            'relatedCocktails' => $this->getRelatedCocktails($hubCategory->term_id),
            'articles' => $this->getArticles($wp_query),
            'pagination' => $wp_query->max_num_pages > 1 ? Pagi::build()->links('components.pagination') : '',
            'totalArticles' => $wp_query->found_posts,
        ];
    }

    protected function getBasicArchiveData($wp_query): array
    {
        return [
            'hubCategory' => null,
            'hubIntro' => null,
            'hubFaq' => [],
            'featuredProducts' => [],
            'featuredArticles' => [],
            'featuredLists' => [],
            'relatedCocktails' => [],
            'articles' => $this->getArticles($wp_query),
            'pagination' => $wp_query->max_num_pages > 1 ? Pagi::build()->links('components.pagination') : '',
            'totalArticles' => $wp_query->found_posts ?? 0,
        ];
    }

    protected function formatTerm($term): array
    {
        $thumbnailId = get_term_meta($term->term_id, 'thumbnail_id', true);

        return [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'description' => $term->description,
            'url' => get_term_link($term),
            'thumbnail_url' => $thumbnailId ? wp_get_attachment_image_url($thumbnailId, 'medium') : null,
        ];
    }

    protected function getFaq(int $termId): array
    {
        $faq = get_field('hub_faq', 'product_cat_' . $termId) ?: [];

        return array_map(fn($item) => [
            'question' => $item['question'] ?? '',
            'answer' => $item['answer'] ?? '',
        ], $faq);
    }

    protected function getFeaturedProducts(int $termId): array
    {
        $productIds = get_field('featured_products', 'product_cat_' . $termId) ?: [];

        if (empty($productIds)) {
            // Fallback: get products from this category
            return $this->getCategoryProducts($termId, 4);
        }

        return array_filter(array_map(
            fn($id) => Product::find($id),
            array_slice($productIds, 0, 8)
        ));
    }

    protected function getCategoryProducts(int $termId, int $limit = 4): array
    {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'tax_query' => [[
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $termId,
            ]],
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ];

        $query = new \WP_Query($args);

        return array_filter(array_map(
            fn($post) => Product::find($post->ID),
            $query->posts
        ));
    }

    protected function getFeaturedArticles(int $termId): array
    {
        $articleIds = get_field('featured_articles', 'product_cat_' . $termId) ?: [];

        if (empty($articleIds)) {
            return [];
        }

        return array_filter(array_map(
            fn($id) => $this->formatArticle(get_post($id)),
            array_slice($articleIds, 0, 6)
        ));
    }

    protected function getFeaturedLists(int $termId): array
    {
        $listIds = get_field('featured_lists', 'product_cat_' . $termId) ?: [];

        if (empty($listIds)) {
            // Fallback: get list-format articles for this category
            return $this->getListArticlesForCategory($termId, 3);
        }

        return array_filter(array_map(
            fn($id) => $this->formatArticle(get_post($id)),
            array_slice($listIds, 0, 6)
        ));
    }

    protected function getListArticlesForCategory(int $termId, int $limit = 3): array
    {
        $args = [
            'post_type' => 'article',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'primary_category',
                    'value' => $termId,
                    'compare' => '=',
                ],
                [
                    'key' => 'content_format',
                    'value' => 'list',
                    'compare' => '=',
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $query = new \WP_Query($args);

        return array_filter(array_map(
            fn($post) => $this->formatArticle($post),
            $query->posts
        ));
    }

    protected function getRelatedCocktails(int $termId): array
    {
        // Get the linked liquor_type for this product_cat
        $liquorTypeId = get_field('linked_liquor_type', 'product_cat_' . $termId);

        if (! $liquorTypeId) {
            return [];
        }

        $args = [
            'post_type' => 'cocktail',
            'post_status' => 'publish',
            'posts_per_page' => 6,
            'tax_query' => [[
                'taxonomy' => 'liquor_type',
                'field' => 'term_id',
                'terms' => $liquorTypeId,
            ]],
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $query = new \WP_Query($args);

        return array_filter(array_map(
            fn($post) => $this->formatCocktail($post),
            $query->posts
        ));
    }

    protected function getArticles($wp_query): array
    {
        if (! $wp_query?->posts) {
            return [];
        }

        return array_filter(array_map(
            fn($post) => $this->formatArticle($post),
            $wp_query->posts
        ));
    }

    protected function formatArticle($post): ?array
    {
        if (! $post || $post->post_type !== 'article') {
            return null;
        }

        $contentFormat = get_field('content_format', $post->ID) ?: 'standard';
        $primaryCategoryId = get_field('primary_category', $post->ID);
        $primaryCategory = null;

        if ($primaryCategoryId) {
            $term = get_term($primaryCategoryId, 'product_cat');
            if ($term && ! is_wp_error($term)) {
                $primaryCategory = [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'url' => get_term_link($term),
                ];
            }
        }

        return [
            'id' => $post->ID,
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'url' => get_permalink($post),
            'excerpt' => $post->post_excerpt ?: wp_trim_words(strip_tags($post->post_content), 30),
            'date' => get_the_date('j F Y', $post),
            'imageId' => get_post_thumbnail_id($post),
            'contentFormat' => $contentFormat,
            'listVariant' => $contentFormat === 'list' ? get_field('list_variant', $post->ID) : null,
            'primaryCategory' => $primaryCategory,
        ];
    }

    protected function formatCocktail($post): ?array
    {
        if (! $post || $post->post_type !== 'cocktail') {
            return null;
        }

        $liquorTypes = get_the_terms($post->ID, 'liquor_type');
        $liquorTypeNames = [];
        if ($liquorTypes && ! is_wp_error($liquorTypes)) {
            $liquorTypeNames = array_map(fn($term) => $term->name, $liquorTypes);
        }

        return [
            'id' => $post->ID,
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'url' => get_permalink($post),
            'excerpt' => $post->post_excerpt ?: wp_trim_words(strip_tags($post->post_content), 20),
            'imageId' => get_post_thumbnail_id($post),
            'prepTime' => get_field('prep_time', $post->ID),
            'difficulty' => get_field('difficulty', $post->ID),
            'liquorTypes' => $liquorTypeNames,
        ];
    }
}
