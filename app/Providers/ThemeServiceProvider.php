<?php

namespace App\Providers;

use App\Services\MegaMenuService;
use App\Services\MetaIndexService;
use App\Services\Search\SearchAnalyticsService;
use App\Services\Woocommerce\AggregatedRatingService;
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
     * Register the search analytics admin page
     */
    public function registerSearchAnalyticsPage(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Zoekanalyse', 'sage'),
            __('Zoekanalyse', 'sage'),
            'manage_woocommerce',
            'search-analytics',
            [$this, 'renderSearchAnalyticsPage']
        );
    }

    /**
     * Render the search analytics page
     */
    public function renderSearchAnalyticsPage(): void
    {
        if (!SearchAnalyticsService::tableExists()) {
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Zoekanalyse', 'sage') . '</h1>';
            echo '<div class="notice notice-warning"><p>';
            echo esc_html__('De zoekanalyse tabel bestaat nog niet. Voer het volgende commando uit:', 'sage');
            echo ' <code>wp acorn search:analytics:migrate</code>';
            echo '</p></div>';
            echo '</div>';
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
            'chartData'
        ))->render();
    }
}
