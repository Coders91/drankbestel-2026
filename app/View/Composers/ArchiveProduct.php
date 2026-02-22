<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

use App\View\Models\Product;
use App\Services\FilterEverythingService;
use Illuminate\Support\Facades\Cache;
use Log1x\Pagi\PagiFacade as Pagi;

class ArchiveProduct extends Composer
{
    protected static $views = [
        'woocommerce.archive-product',
    ];

    public function with(): array
    {
        global $wp_query;

        if (! $wp_query?->posts) {
            return [
                'products' => collect(),
                'pagination' => '',
                'activeFilters' => [],
                'totalProducts' => 0,
                'proposedFilters' => [],
                'soortCategories' => [],
                'filters' => [],
                'activeFilterCount' => 0,
                'selectedChips' => [],
                'resetUrl' => '',
                'moreLessCount' => 0,
                'sortOptions' => [],
                'currentSort' => '',
                'queryVars' => [],
                'maxPages' => 0,
                'currentPage' => 1,
                'nextPageUrl' => '',
            ];
        }

        $pagination = Pagi::build();

        $service = new FilterEverythingService();

        // Detect current term for proposed filters
        $proposedFilters = [];
        $queriedObject = get_queried_object();

        if ($queriedObject instanceof \WP_Term) {
            $taxonomy = $queriedObject->taxonomy;
            if (in_array($taxonomy, ['product_cat', 'product_brand'])) {
                $proposedFilters = $service->getProposedFilters($queriedObject->term_id, $taxonomy);
            }
        }

        $productIds = wp_list_pluck($wp_query->posts, 'ID');
        Product::primeCache($productIds);

        return [
            'products' => collect($wp_query->posts)
                ->map(fn ($post) => Product::find($post->ID))
                ->filter(),
            'queryVars' => $wp_query->query_vars,
            'maxPages' => $wp_query->max_num_pages,
            'currentPage' => max(1, get_query_var('paged', 1)),
            'nextPageUrl' => $pagination->nextPageUrl(),
            'pagination' => $pagination->links('components.pagination'),
            'totalProducts' => $wp_query->found_posts,
            'filters' => $service->getFiltersForView(),
            'activeFilterCount' => $service->getActiveFilterCount(),
            'selectedChips' => $service->getSelectedChips(),
            'resetUrl' => $service->getResetUrl(),
            'moreLessCount' => $service->getMoreLessCount(),
            'sortOptions' => $service->getSortingOptions(),
            'currentSort' => $service->getCurrentSort(),
            'proposedFilters' => $proposedFilters,
            'soortCategories' => $this->getSoortCategories(),
        ];
    }

    /**
     * Get pa_soort terms mapped to product_cat for the category selector.
     * Only returns data for top-level product_cat pages (those with children).
     */
    private function getSoortCategories(): array
    {
        // Only show on product category pages
        if (!is_product_category()) {
            return [];
        }

        $currentTerm = get_queried_object();

        if (!$currentTerm instanceof \WP_Term) {
            return [];
        }

        // Only show on top-level categories (those WITH children)
        $children = get_term_children($currentTerm->term_id, 'product_cat');

        if (empty($children)) {
            return []; // No children, don't show selector
        }

        return Cache::remember("soort_categories_{$currentTerm->term_id}", 3600, function () use ($currentTerm) {
            global $wpdb;

            $soortTerms = $wpdb->get_results($wpdb->prepare("
                SELECT t.term_id, t.name, t.slug, COUNT(DISTINCT tr.object_id) as count
                FROM {$wpdb->terms} t
                INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                INNER JOIN {$wpdb->term_relationships} tr_cat ON tr.object_id = tr_cat.object_id
                INNER JOIN {$wpdb->term_taxonomy} tt_cat ON tr_cat.term_taxonomy_id = tt_cat.term_taxonomy_id
                WHERE tt.taxonomy = 'pa_soort'
                AND tt_cat.taxonomy = 'product_cat'
                AND tt_cat.term_id IN (
                    SELECT term_id FROM {$wpdb->term_taxonomy}
                    WHERE taxonomy = 'product_cat'
                    AND (term_id = %d OR parent = %d)
                )
                GROUP BY t.term_id
                ORDER BY count DESC
            ", $currentTerm->term_id, $currentTerm->term_id));

            if (empty($soortTerms)) {
                return [];
            }

            $categories = [];

            foreach ($soortTerms as $soortTerm) {
                $matchingCat = get_term_by('name', $soortTerm->name, 'product_cat');

                if (!$matchingCat) {
                    continue;
                }

                $thumbnailId = get_term_meta($matchingCat->term_id, 'thumbnail_id', true);
                $imageUrl = $thumbnailId ? wp_get_attachment_image_url($thumbnailId, 'medium') : null;

                $categories[] = [
                    'name' => $soortTerm->name,
                    'slug' => $matchingCat->slug,
                    'url' => get_term_link($matchingCat),
                    'image_url' => $imageUrl,
                    'count' => (int) $soortTerm->count,
                ];
            }

            return $categories;
        });
    }
}
