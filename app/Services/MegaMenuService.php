<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MegaMenuService
{
    /**
     * Cache TTL in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Get top-level product categories for the mega menu.
     */
    public function getMainCategories(): Collection
    {
        return Cache::remember('mega_menu_main_categories', self::CACHE_TTL, function () {
            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'parent' => 0,
                'hide_empty' => true,
                'exclude' => get_option('default_product_cat'),
                'orderby' => 'menu_order',
                'order' => 'ASC',
            ]);

            if (is_wp_error($terms)) {
                return collect();
            }

            return collect($terms)->map(fn ($term) => $this->formatTerm($term, true));
        });
    }

    /**
     * Get child categories for a given parent.
     */
    public function getChildCategories(int $parentId): Collection
    {
        $cacheKey = "mega_menu_children_{$parentId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($parentId) {
            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'parent' => $parentId,
                'hide_empty' => true,
                'orderby' => 'menu_order',
                'order' => 'ASC',
            ]);

            if (is_wp_error($terms)) {
                return collect();
            }

            return collect($terms)->map(fn ($term) => $this->formatTerm($term));
        });
    }

    /**
     * Get categories structured for mega panel display.
     * Returns top-level categories with their children nested.
     */
    public function getCategoriesForMegaPanel(?string $parentSlug = null): Collection
    {
        $cacheKey = "mega_menu_panel_{$parentSlug}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($parentSlug) {
            $parentId = 0;

            if ($parentSlug) {
                $parentTerm = get_term_by('slug', $parentSlug, 'product_cat');
                if ($parentTerm) {
                    $parentId = $parentTerm->term_id;
                }
            }

            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'parent' => $parentId,
                'hide_empty' => true,
                'exclude' => get_option('default_product_cat'),
                'childless' => false,
                'orderby' => 'menu_order',
                'order' => 'ASC',
            ]);

            if (is_wp_error($terms)) {
                return collect();
            }

            return collect($terms)->map(function ($term) {
                $formatted = $this->formatTerm($term);
                $formatted['children'] = $this->getChildCategories($term->term_id);

                return $formatted;
            });
        });
    }

    /**
     * Get featured brands for the mega panel.
     */
    public function getFeaturedBrands(int $limit = 6): Collection
    {
        return Cache::remember('mega_menu_featured_brands', self::CACHE_TTL, function () use ($limit) {
            // Try product_brand taxonomy first
            $terms = get_terms([
                'taxonomy' => 'product_brand',
                'hide_empty' => true,
                'number' => $limit,
                'orderby' => 'count',
                'order' => 'DESC',
            ]);

            // Fallback to pa_merk attribute if product_brand doesn't exist
            if (is_wp_error($terms) || empty($terms)) {
                $terms = get_terms([
                    'taxonomy' => 'pa_merk',
                    'hide_empty' => true,
                    'number' => $limit,
                    'orderby' => 'count',
                    'order' => 'DESC',
                ]);
            }

            if (is_wp_error($terms)) {
                return collect();
            }

            return collect($terms)->map(fn ($term) => $this->formatBrand($term));
        });
    }

    /**
     * Get all brands for the brands page/mega panel.
     */
    public function getAllBrands(): Collection
    {
        return Cache::remember('mega_menu_all_brands', self::CACHE_TTL, function () {
            $terms = get_terms([
                'taxonomy' => 'product_brand',
                'hide_empty' => true,
                'orderby' => 'name',
                'order' => 'ASC',
            ]);

            if (is_wp_error($terms) || empty($terms)) {
                $terms = get_terms([
                    'taxonomy' => 'pa_merk',
                    'hide_empty' => true,
                    'orderby' => 'name',
                    'order' => 'ASC',
                ]);
            }

            if (is_wp_error($terms)) {
                return collect();
            }

            return collect($terms)->map(fn ($term) => $this->formatBrand($term));
        });
    }

    /**
     * Clear all mega menu caches.
     */
    public function clearCache(): void
    {
        Cache::forget('mega_menu_main_categories');
        Cache::forget('mega_menu_featured_brands');
        Cache::forget('mega_menu_all_brands');

        // Clear child category caches
        $mainCategories = get_terms([
            'taxonomy' => 'product_cat',
            'parent' => 0,
            'hide_empty' => false,
        ]);

        if (! is_wp_error($mainCategories)) {
            foreach ($mainCategories as $term) {
                Cache::forget("mega_menu_children_{$term->term_id}");
                Cache::forget("mega_menu_panel_{$term->slug}");
            }
        }
    }

    /**
     * Format a term for display.
     */
    private function formatTerm(object $term, bool $includeImage = false): array
    {
        $data = [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'url' => get_term_link($term),
            'count' => $term->count,
        ];

        if ($includeImage) {
            $thumbnailId = get_term_meta($term->term_id, 'thumbnail_id', true);
            $data['image'] = $thumbnailId ? wp_get_attachment_image_url($thumbnailId, 'thumbnail') : null;
        }

        return $data;
    }

    /**
     * Format a brand term for display.
     */
    private function formatBrand(object $term): array
    {
        $thumbnailId = get_term_meta($term->term_id, 'thumbnail_id', true);

        return [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'url' => get_term_link($term),
            'count' => $term->count,
            'image' => $thumbnailId ? wp_get_attachment_image_url($thumbnailId, 'thumbnail') : null,
        ];
    }
}
