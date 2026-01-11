<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use WP_Term;

class ProductBrand extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var string[]
     */
    protected static $views = [
        'page-merken',
    ];

    /**
     * Fetches, combines, and prepares brand data from both taxonomies.
     *
     * @return array Combined list of brands.
     */
    private function getCombinedBrandsData(): array
    {
        $brands = [];

        // Get 'product_brand' terms (these are linkable and may have images)
        $product_brands = get_terms([
            'taxonomy' => 'product_brand',
            'hide_empty' => true,
        ]);

        if (! is_wp_error($product_brands) && ! empty($product_brands)) {
            foreach ($product_brands as $term) {
                $name_key = mb_strtolower(trim($term->name));
                $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);

                if (! isset($brands[$name_key])) {
                    $brands[$name_key] = [
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'term_id' => $term->term_id,
                        'taxonomy' => 'product_brand',
                        'is_linkable' => true,
                        'url' => get_term_link($term),
                        'thumbnail_id' => $thumbnail_id ?: null,
                        'thumbnail_url' => $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : null,
                    ];
                }
            }
        }

        // Get 'pa_merk' attribute terms (not linkable, no dedicated pages)
        $pa_merks = get_terms([
            'taxonomy' => 'pa_merk',
            'hide_empty' => true,
        ]);

        if (! is_wp_error($pa_merks) && ! empty($pa_merks)) {
            foreach ($pa_merks as $term) {
                $name_key = mb_strtolower(trim($term->name));

                // Only add if not already present from product_brand
                if (! isset($brands[$name_key])) {
                    $brands[$name_key] = [
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'term_id' => $term->term_id,
                        'taxonomy' => 'pa_merk',
                        'is_linkable' => false,
                        'url' => null,
                        'thumbnail_id' => null,
                        'thumbnail_url' => null,
                    ];
                }
            }
        }

        return array_values($brands);
    }

    /**
     * Sorts and groups brands alphabetically.
     *
     * @param  array  $brands  List of brand data arrays.
     * @return array Brands grouped by the first letter.
     */
    private function groupBrandsAlphabetically(array $brands): array
    {
        if (empty($brands)) {
            return [];
        }

        usort($brands, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        $grouped_brands = [];

        foreach ($brands as $brand) {
            $first_letter = mb_strtoupper(mb_substr(trim($brand['name']), 0, 1, 'UTF-8'));

            if (! ctype_alpha($first_letter)) {
                $first_letter = '0-9';
            }

            if (! isset($grouped_brands[$first_letter])) {
                $grouped_brands[$first_letter] = [];
            }

            $grouped_brands[$first_letter][] = $brand;
        }

        // Sort: A-Z first, then 0-9 at the end
        uksort($grouped_brands, function ($a, $b) {
            if ($a === '0-9') {
                return 1;
            }
            if ($b === '0-9') {
                return -1;
            }

            return strcasecmp($a, $b);
        });

        return $grouped_brands;
    }

    /**
     * Get brands that have thumbnail images (for the slider).
     *
     * @param  array  $brands  List of brand data arrays.
     * @return array Brands with thumbnails.
     */
    private function getBrandsWithImages(array $brands): array
    {
        return array_values(array_filter($brands, function ($brand) {
            return ! empty($brand['thumbnail_url']);
        }));
    }

    /**
     * Data to be passed to the view.
     *
     * @return array Data for the view.
     */
    public function with(): array
    {
        $all_brands = $this->getCombinedBrandsData();

        return [
            'all_brands_grouped_alphabetically' => $this->groupBrandsAlphabetically($all_brands),
            'brands_with_images' => $this->getBrandsWithImages($all_brands),
        ];
    }
}
