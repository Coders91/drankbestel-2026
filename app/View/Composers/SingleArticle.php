<?php

namespace App\View\Composers;

use App\Services\MetaIndexService;
use App\View\Models\Product;
use Roots\Acorn\View\Composer;

class SingleArticle extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'single-article',
    ];

    /**
     * Data to be passed to view before rendering.
     */
    public function with(): array
    {
        $post = get_post();

        if (! $post || $post->post_type !== 'article') {
            return [];
        }

        $contentFormat = get_field('content_format', $post->ID) ?: 'standard';
        $primaryCategoryId = get_field('primary_category', $post->ID);
        $primaryCategory = $primaryCategoryId ? get_term($primaryCategoryId, 'product_cat') : null;

        $data = [
            'article' => $post,
            'contentFormat' => $contentFormat,
            'primaryCategory' => $primaryCategory,
            'relatedProducts' => $this->getRelatedProducts($post->ID),
            'relatedBrands' => $this->getRelatedBrands($post->ID),
        ];

        // Add list-specific data
        if ($contentFormat === 'list') {
            $data['listVariant'] = get_field('list_variant', $post->ID);
            $data['listItems'] = $this->getListItems($post->ID);
            $data['lastUpdated'] = get_field('last_updated', $post->ID);
        }

        return $data;
    }

    protected function getRelatedProducts(int $postId): array
    {
        $productIds = get_field('related_products', $postId) ?: [];

        if (empty($productIds)) {
            return [];
        }

        return array_filter(array_map(
            fn($id) => Product::find($id),
            $productIds
        ));
    }

    protected function getRelatedBrands(int $postId): array
    {
        $brandIds = get_field('related_brands', $postId) ?: [];

        if (empty($brandIds)) {
            return [];
        }

        $brands = [];
        foreach ($brandIds as $brandId) {
            $term = get_term($brandId, 'product_brand');
            if ($term && ! is_wp_error($term)) {
                $brands[] = [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'url' => get_term_link($term),
                    'thumbnail_url' => $this->getBrandThumbnail($term->term_id),
                ];
            }
        }

        return $brands;
    }

    protected function getBrandThumbnail(int $termId): ?string
    {
        $thumbnailId = get_term_meta($termId, 'thumbnail_id', true);

        if ($thumbnailId) {
            return wp_get_attachment_image_url($thumbnailId, 'medium');
        }

        return null;
    }

    protected function getListItems(int $postId): array
    {
        $items = get_field('list_items', $postId) ?: [];

        return array_map(function ($item) {

            $product = null;
            if (! empty($item['product'])) {
                $product = Product::find($item['product']);
            }

            return [
                'position' => $item['position'] ?? 0,
                'product' => $product,
                'reason' => $item['reason'] ?? '',
                'criteria' => $item['criteria'] ?? '',
                'pros_cons' => [],
            ];
        }, $items);
    }
}
