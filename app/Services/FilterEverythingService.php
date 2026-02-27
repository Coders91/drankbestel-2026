<?php

namespace App\Services;

use FilterEverything\Filter\Container;
use FilterEverything\Filter\Sorting;

class FilterEverythingService
{
    protected $entityManager;
    protected $urlManager;
    protected bool $isAvailable = false;

    public function __construct()
    {
        if (!class_exists(Container::class)) {
            return;
        }

        $container = Container::instance();
        $this->entityManager = $container->getEntityManager();
        // Use the container's singleton UrlManager to ensure proper state
        $this->urlManager = $container->getUrlManager();
        $this->isAvailable = true;
    }

    /**
     * Check if Filter Everything plugin is available
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable && function_exists('flrt_get_page_related_filters');
    }

    /**
     * Get filters formatted for Blade views
     *
     * @param string $postType The post type to get filters for
     * @param array|null $filters Pre-fetched filters (bypasses page context detection)
     * @param bool $requireCrossCount Whether to filter out terms with no matching products
     * @return array<string, array{label: string, slug: string, settings: array, terms: array}>
     */
    public function getFiltersForView(string $postType = 'product', ?array $filters = null, bool $requireCrossCount = true): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $selectedTerms = $this->getSelectedTerms();

        // Use provided filters or fall back to page-related filters
        if ($filters === null) {
            $filters = flrt_get_page_related_filters();
        }

        if (!is_array($filters)) {
            return [];
        }

        $grouped = [];

        foreach ($filters as $filter) {
            if (!is_array($filter)) {
                continue;
            }

            // Get the entity name and type from the filter (required for URL generation)
            $entityName = $filter['e_name'] ?? null;
            $entityType = $filter['entity'] ?? 'taxonomy';

            if (!$entityName) {
                continue;
            }

            $terms = flrt_get_filter_terms($filter, $postType, $this->entityManager);

            if (!is_array($terms) && !is_object($terms)) {
                continue;
            }

            foreach ($terms as $term) {
                if (!is_object($term)) {
                    continue;
                }

                // Skip terms with no matching products (unless bypassing page context)
                if ($requireCrossCount && ($term->cross_count ?? 0) < 1) {
                    continue;
                }

                $taxonomy = $this->resolveTaxonomy($term, $filter);

                if (!$taxonomy) {
                    continue;
                }

                // Use a unique key based on filter ID to prevent grouping issues
                $filterKey = $filter['ID'] ?? $entityName;

                if (!isset($grouped[$filterKey])) {
                    $grouped[$filterKey] = [
                        'label' => $filter['label'] ?? $this->resolveTaxonomyLabel($taxonomy),
                        'slug' => $taxonomy,
                        'e_name' => $entityName,
                        'entity' => $entityType,
                        'settings' => $this->extractFilterSettings($filter),
                        'terms' => [],
                    ];
                }

                $isActive = isset($selectedTerms[$entityName]) && in_array($term->slug, $selectedTerms[$entityName], true);

                $grouped[$filterKey]['terms'][] = [
                    'id' => $term->term_id,
                    'slug' => $term->slug,
                    'label' => $term->name,
                    'count' => $term->cross_count,
                    'url' => $this->getTermUrl($term->slug, $entityName, $entityType),
                    'active' => $isActive,
                    'parent' => $term->parent ?? 0,
                ];
            }
        }

        // Calculate cumulative counts for rating filters with selected_and_above
        foreach ($grouped as $filterKey => &$filterData) {
            if (($filterData['settings']['view'] ?? '') !== 'rating') {
                continue;
            }

            if (($filterData['settings']['selected_and_above'] ?? 'no') !== 'yes') {
                continue;
            }

            // Sort terms by rating value (rated-1, rated-2, etc.) to ensure correct order
            usort($filterData['terms'], function ($a, $b) {
                preg_match('/rated-(\d+)/', $a['slug'], $matchA);
                preg_match('/rated-(\d+)/', $b['slug'], $matchB);
                $ratingA = isset($matchA[1]) ? (int) $matchA[1] : 0;
                $ratingB = isset($matchB[1]) ? (int) $matchB[1] : 0;

                return $ratingA <=> $ratingB;
            });

            // Calculate cumulative counts (each rating includes all higher ratings)
            $cumulativeCount = 0;
            $termCount = count($filterData['terms']);

            for ($i = $termCount - 1; $i >= 0; $i--) {
                $cumulativeCount += $filterData['terms'][$i]['count'];
                $filterData['terms'][$i]['count'] = $cumulativeCount;
            }
        }
        unset($filterData);

        return $grouped;
    }

    /**
     * Extract filter settings from raw filter array
     */
    protected function extractFilterSettings(array $filter): array
    {
        return [
            'view' => $filter['view'] ?? 'checkboxes',
            'collapse' => $filter['collapse'] ?? 'no',
            'more_less' => $filter['more_less'] ?? 'no',
            'hierarchy' => $filter['hierarchy'] ?? 'no',
            'search' => $filter['search'] ?? 'no',
            'selected_and_above' => $filter['selected_and_above'] ?? 'no',
            'tooltip' => $filter['tooltip'] ?? '',
        ];
    }

    /**
     * Get filters for a specific filter set ID (bypasses page context)
     *
     * Useful for displaying filters on pages that aren't product archives.
     * Note: cross_count will be 0 since there's no query context, so counts won't be accurate.
     *
     * @param int|array $filterSetIds Single ID or array of filter set IDs
     * @param string $postType The post type to get filters for
     * @return array<string, array{label: string, slug: string, settings: array, terms: array}>
     */
    public function getFiltersForFilterSet(int|array $filterSetIds, string $postType = 'product'): array
    {
        if (!$this->isAvailable() || !$this->entityManager) {
            return [];
        }

        $sets = collect((array) $filterSetIds)
            ->map(fn($id) => ['ID' => $id])
            ->all();

        $filters = $this->entityManager->getSetsRelatedFilters($sets);

        // Pass requireCrossCount=false since we don't have a query context
        return $this->getFiltersForView($postType, $filters, false);
    }

    /**
     * Get all filter set IDs that have configured filters
     *
     * @return array<int>
     */
    public function getAvailableFilterSetIds(): array
    {
        if (!$this->entityManager) {
            return [];
        }

        $allFilters = $this->entityManager->getAllConfiguredFilters();
        $parentIds = array_unique(array_column($allFilters, 'parent'));

        return array_map('intval', array_values($parentIds));
    }

    /**
     * Get the first available filter set ID (convenience method)
     *
     * @return int|null
     */
    public function getDefaultFilterSetId(): ?int
    {
        $ids = $this->getAvailableFilterSetIds();
        return $ids[0] ?? null;
    }

    /**
     * Get the number of items to show before "more/less" toggle
     */
    public function getMoreLessCount(): int
    {
        return function_exists('flrt_more_less_count') ? flrt_more_less_count() : 5;
    }

    /**
     * Get currently selected filter terms grouped by taxonomy
     *
     * @return array<string, array<string>> taxonomy => [slugs]
     */
    public function getSelectedTerms(): array
    {
        if (!function_exists('flrt_selected_filter_terms')) {
            return [];
        }

        $selected = flrt_selected_filter_terms();

        if (!is_array($selected)) {
            return [];
        }

        $result = [];

        foreach ($selected as $group) {
            if (!is_array($group) || !isset($group['e_name'], $group['values'])) {
                continue;
            }

            $taxonomy = $group['e_name'];
            $values = is_array($group['values']) ? $group['values'] : [];

            $result[$taxonomy] = $values;
        }

        return $result;
    }

    /**
     * Get URL for a specific term filter
     *
     * @param string $slug The term slug
     * @param string $entityName The filter entity name (e_name from FilterEverything)
     * @param string $entityType The filter entity type (entity from FilterEverything)
     */
    public function getTermUrl(string $slug, string $entityName, string $entityType = 'taxonomy'): string
    {
        if (!$this->urlManager) {
            return '#';
        }

        return $this->urlManager->getTermUrl($slug, $entityName, $entityType);
    }

    /**
     * Get the URL to reset all filters
     */
    public function getResetUrl(): string
    {
        if (!$this->isAvailable()) {
            return '';
        }

        // Get base URL without any filter parameters
        global $wp;
        return home_url($wp->request);
    }

    /**
     * Check if any filters are currently active
     */
    public function hasActiveFilters(): bool
    {
        return !empty($this->getSelectedTerms());
    }

    /**
     * Get count of active filters
     */
    public function getActiveFilterCount(): int
    {
        $selected = $this->getSelectedTerms();
        $count = 0;

        foreach ($selected as $slugs) {
            $count += count($slugs);
        }

        return $count;
    }

    /**
     * Get selected filter chips for display
     *
     * @return array<int, array{link: string, name: string, class: string, label: string, rating?: int}>
     */
    public function getSelectedChips(): array
    {
        if (!function_exists('flrt_selected_filter_chips')) {
            return [];
        }

        $chips = flrt_selected_filter_chips(false) ?: [];

        // Check if any rating filter has selected_and_above enabled
        $filters = flrt_get_page_related_filters();
        $selectedAndAbove = false;

        foreach ($filters as $filter) {
            if (($filter['view'] ?? '') === 'rating' &&
                ($filter['selected_and_above'] ?? 'no') === 'yes') {
                $selectedAndAbove = true;
                break;
            }
        }

        if (!$selectedAndAbove) {
            return $chips;
        }

        // Consolidate rating chips into one
        $ratingChips = [];
        $otherChips = [];

        foreach ($chips as $chip) {
            if (isset($chip['rating'])) {
                $ratingChips[] = $chip;
            } else {
                $otherChips[] = $chip;
            }
        }

        // If only one or no rating chips, return as-is
        if (count($ratingChips) <= 1) {
            return $chips;
        }

        // Find lowest rating and use its removal URL
        usort($ratingChips, fn($a, $b) => (int) $a['rating'] <=> (int) $b['rating']);
        $lowestRatingChip = $ratingChips[0];

        // Return consolidated chip with other chips
        return array_merge($otherChips, [$lowestRatingChip]);
    }

    /**
     * Get sorting options from configured widget or defaults
     *
     * @return array<int, array{label: string, value: string, url: string}>
     */
    public function getSortingOptions(): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $config = $this->getSortingConfig();

        if (empty($config['orderbies'])) {
            return [];
        }

        $options = [];
        $baseUrl = $this->urlManager->getFormActionOrFullPageUrl();
        $queryParams = function_exists('flrt_get_query_string_parameters')
            ? flrt_get_query_string_parameters()
            : [];

        // Remove existing sort params from base
        unset($queryParams['ordr'], $queryParams['orderby'], $queryParams['product_orderby']);

        foreach ($config['orderbies'] as $i => $orderby) {
            $title = $config['titles'][$i] ?? '';
            $order = $config['orders'][$i] ?? 'asc';
            $metaKey = $config['meta_keys'][$i] ?? '';

            $value = $this->buildSortOptionValue($orderby, $order, $metaKey);

            // Build URL with sort parameter
            $urlParams = $queryParams;
            if ($value !== 'default') {
                $urlParams['ordr'] = $value;
            }

            $options[] = [
                'label' => $title,
                'value' => $value,
                'url' => add_query_arg($urlParams, $baseUrl),
            ];
        }

        return $options;
    }

    /**
     * Get current sort value from URL
     */
    public function getCurrentSort(): string
    {
        return isset($_GET['ordr']) ? sanitize_text_field($_GET['ordr']) : 'default';
    }

    /**
     * Build sort option value (mirrors flrt_sorting_option_value)
     */
    protected function buildSortOptionValue(string $orderby, string $order, string $metaKey = ''): string
    {
        $value = $orderby;

        // Handle meta key sorting
        if (in_array($orderby, ['m', 'n'], true) && $metaKey) {
            $value .= $metaKey;
        }

        // Append order direction for desc
        if (strtolower($order) === 'desc') {
            $value .= '-desc';
        }

        return $value;
    }

    /**
     * Get sorting configuration from widget or defaults
     */
    protected function getSortingConfig(): array
    {
        // Try to get from configured widget
        $widgetOptions = get_option('widget_wpc_sorting_widget', []);

        // Find first configured widget instance
        foreach ($widgetOptions as $key => $instance) {
            if (is_numeric($key) && !empty($instance['orderbies'])) {
                return [
                    'titles' => $instance['titles'] ?? [],
                    'orderbies' => $instance['orderbies'] ?? [],
                    'orders' => $instance['orders'] ?? [],
                    'meta_keys' => $instance['meta_keys'] ?? [],
                ];
            }
        }

        // Fall back to defaults from Sorting class
        if (class_exists(Sorting::class)) {
            $sorting = new Sorting();
            return $sorting->getSortingDefaults();
        }

        return [];
    }

    /**
     * Get filter term choices for admin ACF field
     *
     * Returns all taxonomy-based filter terms with product counts
     * scoped to a specific category or brand term.
     *
     * @param int $termId The term ID to scope counts to
     * @param string $taxonomy The taxonomy (product_cat or product_brand)
     * @return array<int, string> term_id => "Term Name (count)"
     */
    public function getProposedFilterChoices(int $termId, string $taxonomy): array
    {
        if (!$this->isAvailable() || !$this->entityManager) {
            return [];
        }

        global $wpdb;

        $filterSetIds = $this->getAvailableFilterSetIds();

        if (empty($filterSetIds)) {
            return [];
        }

        $sets = array_map(fn($id) => ['ID' => $id], $filterSetIds);
        $filters = $this->entityManager->getSetsRelatedFilters($sets);

        $choices = [];

        foreach ($filters as $filter) {
            $entityName = $filter['e_name'] ?? null;
            $entity = $filter['entity'] ?? null;

            if ($entity !== 'taxonomy' || !$entityName) {
                continue;
            }

            $results = $wpdb->get_results($wpdb->prepare("
                SELECT DISTINCT t.term_id, t.name, COUNT(DISTINCT p.ID) as count
                FROM {$wpdb->terms} AS t
                INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
                INNER JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->posts} AS p ON p.ID = tr.object_id
                WHERE tt.taxonomy = %s
                AND p.post_type = 'product'
                AND p.post_status = 'publish'
                AND EXISTS (
                    SELECT 1 FROM {$wpdb->term_relationships} AS rel
                    INNER JOIN {$wpdb->term_taxonomy} AS tax ON rel.term_taxonomy_id = tax.term_taxonomy_id
                    WHERE rel.object_id = p.ID AND tax.term_id = %d
                )
                GROUP BY t.term_id
                HAVING count > 0
                ORDER BY t.name ASC
            ", $entityName, $termId));

            foreach ($results as $result) {
                if (!isset($choices[$result->term_id])) {
                    $choices[$result->term_id] = sprintf('%s (%d)', $result->name, $result->count);
                }
            }
        }

        return $choices;
    }

    /**
     * Get proposed filters for a taxonomy term with current counts
     *
     * @param int $termId The term ID to get proposed filters for
     * @param string $taxonomy The taxonomy (product_cat or product_brand)
     * @return array<int, array{id: int, name: string, url: string, count: int, active: bool, taxonomy: string, e_name: string}>
     */
    public function getProposedFilters(int $termId, string $taxonomy): array
    {
        if (!$this->isAvailable() || !function_exists('get_field')) {
            return [];
        }

        $proposedTermIds = get_field('proposed_filters', $taxonomy . '_' . $termId);

        if (empty($proposedTermIds) || !is_array($proposedTermIds)) {
            return [];
        }

        $filters = $this->getFiltersForView();
        $selectedTerms = $this->getSelectedTerms();

        $termLookup = [];
        foreach ($filters as $filterKey => $filter) {
            foreach ($filter['terms'] ?? [] as $term) {
                $termLookup[$term['id']] = array_merge($term, [
                    'taxonomy' => $filter['slug'],
                    'e_name' => $filter['e_name'],
                    'entity' => $filter['entity'] ?? 'taxonomy',
                ]);
            }
        }

        $result = [];
        foreach ($proposedTermIds as $proposedTermId) {
            $proposedTermId = (int) $proposedTermId;

            if (!isset($termLookup[$proposedTermId])) {
                continue;
            }

            $termData = $termLookup[$proposedTermId];

            if (($termData['count'] ?? 0) < 1) {
                continue;
            }

            $entityName = $termData['e_name'];
            $isActive = isset($selectedTerms[$entityName])
                && in_array($termData['slug'], $selectedTerms[$entityName], true);

            $result[] = [
                'id' => $proposedTermId,
                'name' => $termData['label'],
                'slug' => $termData['slug'],
                'url' => $termData['url'],
                'count' => $termData['count'],
                'active' => $isActive,
                'taxonomy' => $termData['taxonomy'],
                'e_name' => $entityName,
            ];
        }

        return $result;
    }

    /**
     * Resolve taxonomy from term object or filter array
     */
    protected function resolveTaxonomy(object $term, array $filter): ?string
    {
        return $term->taxonomy
            ?? $term->entity_name
            ?? $term->e_name
            ?? $filter['entity_name']
            ?? $filter['e_name']
            ?? null;
    }

    /**
     * Get human-readable taxonomy label
     */
    protected function resolveTaxonomyLabel(string $taxonomy): string
    {
        if (str_starts_with($taxonomy, 'pa_')) {
            return wc_attribute_label($taxonomy);
        }

        $tax = get_taxonomy($taxonomy);

        return $tax->labels->singular_name ?? ucfirst(str_replace('_', ' ', $taxonomy));
    }

    public function isFilteredPage(): bool {
        return function_exists('flrt_is_filter_request') && flrt_is_filter_request();
    }

}
