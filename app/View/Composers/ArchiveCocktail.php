<?php

namespace App\View\Composers;

use Log1x\Pagi\PagiFacade as Pagi;
use Roots\Acorn\View\Composer;

class ArchiveCocktail extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'archive-cocktail',
    ];

    /**
     * Data to be passed to view before rendering.
     */
    public function with(): array
    {
        global $wp_query;

        return [
            'cocktails' => $this->getCocktails($wp_query),
            'liquorTypes' => $this->getLiquorTypes(),
            'cocktailTypes' => $this->getCocktailTypes(),
            'pagination' => $wp_query->max_num_pages > 1 ? Pagi::build()->links('components.pagination') : '',
            'totalCocktails' => $wp_query->found_posts,
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

    protected function getLiquorTypes(): array
    {
        $terms = get_terms([
            'taxonomy' => 'liquor_type',
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC',
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
