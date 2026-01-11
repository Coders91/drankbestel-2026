<?php

namespace App\View\Composers;

use App\View\Models\Product;
use Roots\Acorn\View\Composer;

class SingleCocktail extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'single-cocktail',
    ];

    /**
     * Data to be passed to view before rendering.
     */
    public function with(): array
    {
        $post = get_post();

        if (! $post || $post->post_type !== 'cocktail') {
            return [];
        }

        return [
            'cocktail' => $post,
            'prepTime' => get_field('prep_time', $post->ID),
            'servings' => get_field('servings', $post->ID) ?: 1,
            'difficulty' => get_field('difficulty', $post->ID),
            'glassType' => get_field('glass_type', $post->ID),
            'garnish' => get_field('garnish', $post->ID),
            'ingredients' => $this->getIngredients($post->ID),
            'instructions' => get_field('instructions', $post->ID),
            'tips' => get_field('tips', $post->ID),
            'brandAssociation' => $this->getBrandAssociation($post->ID),
            'liquorTypes' => $this->getLiquorTypes($post->ID),
            'cocktailTypes' => $this->getCocktailTypes($post->ID),
            'linkedProducts' => $this->getLinkedProducts($post->ID),
        ];
    }

    protected function getIngredients(int $postId): array
    {
        $liquors = get_field('liquors', $postId) ?: [];

        return array_map(function ($item) {
            $product = null;
            if (! empty($item['linked_product'])) {
                $product = Product::find($item['linked_product']);
            }

            $liquorType = null;
            if (! empty($item['liquor_type'])) {
                $term = get_term($item['liquor_type'], 'liquor_type');
                if ($term && ! is_wp_error($term)) {
                    $liquorType = [
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'url' => get_term_link($term),
                    ];
                }
            }

            return [
                'quantity' => $item['quantity'] ?? '',
                'unit' => $item['unit'] ?? 'ml',
                'name' => $item['ingredient_name'] ?? '',
                'liquor_type' => $liquorType,
                'product' => $product,
            ];
        }, $liquors);
    }

    protected function getBrandAssociation(int $postId): ?array
    {
        $brandId = get_field('brand_association', $postId);

        if (! $brandId) {
            return null;
        }

        $term = get_term($brandId, 'product_brand');

        if (! $term || is_wp_error($term)) {
            return null;
        }

        $thumbnailId = get_term_meta($term->term_id, 'thumbnail_id', true);

        return [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'url' => get_term_link($term),
            'thumbnail_url' => $thumbnailId ? wp_get_attachment_image_url($thumbnailId, 'medium') : null,
        ];
    }

    protected function getLiquorTypes(int $postId): array
    {
        $terms = get_the_terms($postId, 'liquor_type');

        if (! $terms || is_wp_error($terms)) {
            return [];
        }

        return array_map(fn($term) => [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'url' => get_term_link($term),
        ], $terms);
    }

    protected function getCocktailTypes(int $postId): array
    {
        $terms = get_the_terms($postId, 'cocktail_type');

        if (! $terms || is_wp_error($terms)) {
            return [];
        }

        return array_map(fn($term) => [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'url' => get_term_link($term),
        ], $terms);
    }

    protected function getLinkedProducts(int $postId): array
    {
        $liquors = get_field('liquors', $postId) ?: [];
        $products = [];
        $seenIds = [];

        foreach ($liquors as $item) {
            if (! empty($item['linked_product']) && ! in_array($item['linked_product'], $seenIds)) {
                $product = Product::find($item['linked_product']);
                if ($product) {
                    $products[] = $product;
                    $seenIds[] = $item['linked_product'];
                }
            }
        }

        return $products;
    }
}
