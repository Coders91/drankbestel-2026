<?php

namespace App\View\Factories;

use App\View\Models\ProductAttribute;
use WC_Product;
use WP_Term;

class ProductAttributeFactory
{
    public static function build(WC_Product $product): array
    {
        $attributes = [];

        $attributes_tax_slugs = array_keys( wc_get_attribute_taxonomy_labels() );
        $taxonomies = array_filter( array_map( 'wc_attribute_taxonomy_name', $attributes_tax_slugs ));

        foreach ($taxonomies as $taxonomy) {
            $value = self::getSingleValue($product, $taxonomy);

            if (!$value) {
                continue;
            }

            [$value, $url] = self::resolveValueAndUrl(
                $product,
                $taxonomy,
                $value
            );

            $attributes[$taxonomy] = new ProductAttribute(
                key: $taxonomy,
                label: self::formatLabel($taxonomy),
                value: $value,
                url: $url
            );
        }

        $productTag = self::buildProductTag($product);

        if ($productTag) {
            $attributes['product_tag'] = $productTag;
        }

        return array_merge(
            $attributes,
            self::buildExtraFields($product)
        );
    }

    private static function getSingleValue(WC_Product $product, string $taxonomy): string
    {
        return trim(explode(',', $product->get_attribute($taxonomy))[0] ?? '');
    }

    private static function resolveValueAndUrl(
        WC_Product $product,
        string $taxonomy,
        string $value
    ): array {
        $url = null;
        switch ($taxonomy) {
            case 'pa_soort':
                $url = self::matchTermLink($product, 'product_cat', $value);
                break;

            case 'pa_merk':
                $url = self::firstTermLink($product, 'product_brand');
                break;

            case 'pa_land':
                $flag = get_svg(
                    'resources.images.icons.flags.' . sanitize_title($value), 'size-4 rounded'
                );

                if (str_contains($flag, '<svg')) {
                    $value = $flag . $value;
                }
                break;
            default:
                $url = self::matchTermMetaUrl($product, $taxonomy, $value);
        }

        return [$value, $url];
    }

    private static function matchTermLink(
        WC_Product $product,
        string $taxonomy,
        string $value
    ): ?string {
        foreach (wp_get_post_terms($product->get_id(), $taxonomy) as $term) {
            if ($term instanceof WP_Term && $term->name === $value) {
                return get_term_link($term);
            }
        }

        return null;
    }

    private static function firstTermLink(
        WC_Product $product,
        string $taxonomy
    ): ?string {
        foreach (wp_get_post_terms($product->get_id(), $taxonomy) as $term) {
            if ($term instanceof WP_Term) {
                return get_term_link($term);
            }
        }

        return null;
    }

    private static function matchTermMetaUrl(
        WC_Product $product,
        string $taxonomy,
        string $value
    ): ?string {
        foreach (wp_get_post_terms($product->get_id(), $taxonomy) as $term) {
            if ($term instanceof WP_Term && $term->name === $value) {
                return get_field(
                    'pa_url',
                    $taxonomy . '_' . $term->term_id
                ) ?: null;
            }
        }

        return null;
    }

    private static function formatLabel(string $taxonomy): string
    {
        return ucfirst(str_replace('pa_', '', $taxonomy));
    }

    private static function buildProductTag(WC_Product $product): ?ProductAttribute
    {
        $terms = wp_get_post_terms($product->get_id(), 'product_tag');

        if (empty($terms) || !($terms[0] instanceof WP_Term)) {
            return null;
        }

        $term = $terms[0]; // only allow one

        return new ProductAttribute(
            key: 'product_tag',
            label: __('Type', 'sage'),
            value: $term->name,
            url: get_term_link($term)
        );
    }

    private static function buildExtraFields(WC_Product $product): array
    {
        $id = $product->get_id();

        return [
            'product_contents' => new ProductAttribute(
                'product_contents',
                'Inhoud',
                (string) get_field('product_contents', $id)
            ),
            'product_alcoholpercentage' => new ProductAttribute(
                'product_alcoholpercentage',
                'Alcoholpercentage',
                (string) get_field('product_alcoholpercentage', $id)
            ),
            'product_ean' => new ProductAttribute(
                'product_ean',
                'EAN',
                (string) get_field('product_ean', $id)
            ),
        ];
    }

}
