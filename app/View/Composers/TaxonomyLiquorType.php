<?php

namespace App\View\Composers;

use Log1x\Pagi\PagiFacade as Pagi;
use Roots\Acorn\View\Composer;

class TaxonomyLiquorType extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'taxonomy-liquor_type',
    ];

    /**
     * Data to be passed to view before rendering.
     */
    public function with(): array
    {
        global $wp_query;

        $term = get_queried_object();

        if (! $term instanceof \WP_Term) {
            return $this->getEmptyData();
        }

        return [
            'liquorType' => $this->formatTerm($term),
            'cocktails' => $this->getCocktails($wp_query),
            'cocktailTypes' => $this->getCocktailTypes(),
            'pagination' => $wp_query->max_num_pages > 1 ? Pagi::build()->links('components.pagination') : '',
            'totalCocktails' => $wp_query->found_posts,
        ];
    }

    protected function getEmptyData(): array
    {
        return [
            'liquorType' => null,
            'cocktails' => [],
            'cocktailTypes' => [],
            'pagination' => '',
            'totalCocktails' => 0,
        ];
    }

    protected function formatTerm(\WP_Term $term): array
    {
        $thumbnailId = get_term_meta($term->term_id, 'thumbnail_id', true);

        return [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'description' => $term->description,
            'count' => $term->count,
            'url' => get_term_link($term),
            'thumbnail_url' => $thumbnailId ? wp_get_attachment_image_url($thumbnailId, 'medium') : null,
        ];
    }

    protected function getCocktails($wp_query): array
    {
        if (! $wp_query?->posts) {
            return [];
        }

        return array_filter(array_map(
            fn($post) => $this->formatCocktail($post),
            $wp_query->posts
        ));
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

        $cocktailTypes = get_the_terms($post->ID, 'cocktail_type');
        $cocktailTypeNames = [];
        if ($cocktailTypes && ! is_wp_error($cocktailTypes)) {
            $cocktailTypeNames = array_map(fn($term) => $term->name, $cocktailTypes);
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
            'cocktailTypes' => $cocktailTypeNames,
        ];
    }

    /**
     * Get all cocktail_type terms for filtering
     */
    protected function getCocktailTypes(): array
    {
        $terms = get_terms([
            'taxonomy' => 'cocktail_type',
            'hide_empty' => true,
        ]);

        if (is_wp_error($terms)) {
            return [];
        }

        return array_map(fn($term) => [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'count' => $term->count,
            'url' => get_term_link($term),
        ], $terms);
    }
}
