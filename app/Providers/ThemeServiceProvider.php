<?php

namespace App\Providers;

use App\Services\MegaMenuService;
use App\Services\MetaIndexService;
use App\Services\Search\SearchAnalyticsService;
use App\Services\Woocommerce\AggregatedRatingService;
use App\Support\Search\SynonymsHandler;
use Illuminate\Support\Facades\Cache;
use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        // Register MegaMenuService as singleton
        $this->app->singleton(MegaMenuService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Register search analytics admin page
        add_action('admin_menu', [$this, 'registerSearchAnalyticsPage']);

        // Product filtering via URL parameters
        add_action('pre_get_posts', [$this, 'filterProductsByUrlParams']);

        // Register aggregated rating sync hooks
        AggregatedRatingService::register();

        // Register article rewrite rules
        add_action('init', [$this, 'registerArticleRewriteRules']);

        // Register article_hub query var
        add_filter('query_vars', [$this, 'registerArticleQueryVars']);

        // Flush rewrite rules on theme activation
        add_action('after_switch_theme', 'flush_rewrite_rules');

        // Update meta indexes on ACF save
        add_action('acf/save_post', [$this, 'updateMetaIndexes'], 20);

        // Invalidate caches when product categories change
        add_action('created_product_cat', [$this, 'invalidateCategoryCaches']);
        add_action('edited_product_cat', [$this, 'invalidateCategoryCaches']);
        add_action('delete_product_cat', [$this, 'invalidateCategoryCaches']);

        // Invalidate caches when product brands change
        add_action('created_product_brand', fn () => app(MegaMenuService::class)->clearCache());
        add_action('edited_product_brand', fn () => app(MegaMenuService::class)->clearCache());
        add_action('delete_product_brand', fn () => app(MegaMenuService::class)->clearCache());

        // Invalidate soort categories cache when pa_soort terms or product relationships change
        add_action('edited_pa_soort', [$this, 'invalidateSoortCaches']);
        add_action('created_pa_soort', [$this, 'invalidateSoortCaches']);
        add_action('delete_pa_soort', [$this, 'invalidateSoortCaches']);
        add_action('woocommerce_update_product', [$this, 'invalidateSoortCaches']);

        // Invalidate shipping caches when WooCommerce shipping zones change
        add_action('woocommerce_after_shipping_zone_object_save', [$this, 'invalidateShippingCaches']);
    }

    /**
     * Invalidate mega menu and soort category caches when product categories change.
     */
    public function invalidateCategoryCaches(): void
    {
        app(MegaMenuService::class)->clearCache();
        $this->invalidateSoortCaches();
    }

    /**
     * Invalidate all soort_categories_* cache keys.
     */
    public function invalidateSoortCaches(): void
    {
        $topLevelTerms = get_terms([
            'taxonomy' => 'product_cat',
            'parent' => 0,
            'hide_empty' => false,
            'fields' => 'ids',
        ]);

        if (!is_wp_error($topLevelTerms)) {
            foreach ($topLevelTerms as $termId) {
                Cache::forget("soort_categories_{$termId}");
            }
        }
    }

    /**
     * Invalidate shipping-related caches.
     */
    public function invalidateShippingCaches(): void
    {
        Cache::forget('free_shipping_amount');
        Cache::forget('flat_rate_cost');
    }

    /**
     * Update flattened meta indexes for articles and cocktails
     */
    public function updateMetaIndexes(int $postId): void
    {
        $postType = get_post_type($postId);

        if (! in_array($postType, ['article', 'cocktail'])) {
            return;
        }

        $service = app(MetaIndexService::class);

        if ($postType === 'cocktail') {
            $service->updateCocktailProductIndex($postId);
        }

        if ($postType === 'article') {
            $service->updateArticleProductIndex($postId);
        }
    }

    /**
     * Register rewrite rules for article URLs
     */
    public function registerArticleRewriteRules(): void
    {
        // Single article: /sterke-drank/{category}/{slug}/
        add_rewrite_rule(
            '^sterke-drank/([^/]+)/([^/]+)/?$',
            'index.php?article=$matches[2]',
            'top'
        );

        // Article hub page: /sterke-drank/{category}/
        add_rewrite_rule(
            '^sterke-drank/([^/]+)/?$',
            'index.php?post_type=article&article_hub=$matches[1]',
            'top'
        );
    }

    /**
     * Register query vars for article hub pages
     */
    public function registerArticleQueryVars(array $vars): array
    {
        $vars[] = 'article_hub';

        return $vars;
    }

    /**
     * Filter products based on URL query parameters
     */
    public function filterProductsByUrlParams(\WP_Query $query): void
    {
        // Only modify main query on product archives (front-end only)
        if (is_admin() || ! $query->is_main_query()) {
            return;
        }

        // Only on shop/product archives
        if (! is_shop() && ! is_product_category() && ! is_product_tag()) {
            return;
        }

        // Get category filter from URL
        $categoryFilters = $_GET['category'] ?? [];

        if (! is_array($categoryFilters)) {
            $categoryFilters = [$categoryFilters];
        }

        $categoryFilters = array_filter(array_map('sanitize_title', $categoryFilters));

        if (empty($categoryFilters)) {
            return;
        }

        // Get existing tax query
        $taxQuery = $query->get('tax_query') ?: [];

        // Add category filter
        $taxQuery[] = [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $categoryFilters,
            'operator' => 'IN',
        ];

        $taxQuery['relation'] = 'AND';
        $query->set('tax_query', $taxQuery);
    }

    /**
     * Register the search admin page
     */
    public function registerSearchAnalyticsPage(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Zoeken', 'sage'),
            __('Zoeken', 'sage'),
            'manage_woocommerce',
            'search-analytics',
            [$this, 'renderSearchPage']
        );
    }

    /**
     * Render the search admin page with tabs
     */
    public function renderSearchPage(): void
    {
        $tab = sanitize_text_field($_GET['tab'] ?? 'analyse');

        // Handle synonyms save
        if ($tab === 'synoniemen' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSynonymsSave();
        }

        // Handle attributes save
        if ($tab === 'attributen' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleAttributesSave();
        }

        $tabs = [
            'analyse' => __('Analyse', 'sage'),
            'synoniemen' => __('Synoniemen', 'sage'),
            'attributen' => __('Attributen', 'sage'),
        ];

        $pageUrl = admin_url('admin.php?page=search-analytics');

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Zoeken', 'sage') . '</h1>';

        // Render tab navigation
        echo '<nav class="nav-tab-wrapper">';
        foreach ($tabs as $slug => $label) {
            $active = $tab === $slug ? ' nav-tab-active' : '';
            $url = $slug === 'analyse' ? $pageUrl : $pageUrl . '&tab=' . $slug;
            echo '<a href="' . esc_url($url) . '" class="nav-tab' . $active . '">' . esc_html($label) . '</a>';
        }
        echo '</nav>';

        // Render tab content
        match ($tab) {
            'synoniemen' => $this->renderSynonymsTab(),
            'attributen' => $this->renderAttributesTab(),
            default => $this->renderAnalyticsTab(),
        };

        echo '</div>';
    }

    /**
     * Render the analytics tab content
     */
    protected function renderAnalyticsTab(): void
    {
        if (! SearchAnalyticsService::tableExists()) {
            echo '<div class="notice notice-warning"><p>';
            echo esc_html__('De zoekanalyse tabel bestaat nog niet. Voer het volgende commando uit:', 'sage');
            echo ' <code>wp acorn search:analytics:migrate</code>';
            echo '</p></div>';

            return;
        }

        $analytics = app(SearchAnalyticsService::class);
        $stats = $analytics->getSearchStats('30 days');
        $popularSearches = $analytics->getPopularSearches(15, '30 days');
        $zeroResultSearches = $analytics->getZeroResultSearches(15, '30 days');
        $chartData = $analytics->getSearchesPerDay(30);

        echo view('admin.search-analytics', compact(
            'stats',
            'popularSearches',
            'zeroResultSearches',
            'chartData',
        ))->render();
    }

    /**
     * Render the synonyms configuration tab
     */
    protected function renderSynonymsTab(): void
    {
        $synonymsText = get_option('search_synonyms', config('search.synonyms', ''));
        $handler = new SynonymsHandler($synonymsText);
        $groups = $handler->getGroups();

        echo view('admin.search-synonyms', compact(
            'synonymsText',
            'groups',
        ))->render();
    }

    /**
     * Handle saving synonyms from the admin form
     */
    /**
     * Render the attributes configuration tab
     */
    protected function renderAttributesTab(): void
    {
        $attributeTaxonomies = wc_get_attribute_taxonomies();
        $selectedAttributes = get_option('search_attribute_taxonomies', []);

        if (! is_array($selectedAttributes)) {
            $selectedAttributes = [];
        }

        echo view('admin.search-attributes', compact(
            'attributeTaxonomies',
            'selectedAttributes',
        ))->render();
    }

    /**
     * Handle saving attribute taxonomies from the admin form
     */
    protected function handleAttributesSave(): void
    {
        if (! wp_verify_nonce($_POST['_wpnonce'] ?? '', 'save_search_attributes')) {
            wp_die(__('Ongeldige beveiligingstoken.', 'sage'));
        }

        $selected = array_map('sanitize_text_field', $_POST['attribute_taxonomies'] ?? []);
        update_option('search_attribute_taxonomies', $selected);

        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo esc_html__('Attributen opgeslagen.', 'sage');
            echo '</p></div>';
        });
    }

    protected function handleSynonymsSave(): void
    {
        if (! wp_verify_nonce($_POST['_wpnonce'] ?? '', 'save_search_synonyms')) {
            wp_die(__('Ongeldige beveiligingstoken.', 'sage'));
        }

        $synonymsText = sanitize_textarea_field($_POST['synonyms'] ?? '');
        update_option('search_synonyms', $synonymsText);

        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo esc_html__('Synoniemen opgeslagen.', 'sage');
            echo '</p></div>';
        });
    }
}
