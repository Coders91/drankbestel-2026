<?php

namespace App\Services\Lightspeed;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use WC_Product;
use WP_Query;

class ProductMappingCache
{
    private string $cacheKey;

    public function __construct(
        private int $ttl = 3600,
        private string $keyPrefix = 'lightspeed'
    ) {
        $this->cacheKey = $this->keyPrefix . ':product_mappings';
    }

    /**
     * Initialize WordPress hooks for cache invalidation
     */
    public function initializeHooks(): void
    {
        add_action('woocommerce_new_product', [$this, 'handleNewProduct'], 10, 1);
        add_action('woocommerce_update_product', [$this, 'handleProductUpdate'], 10, 2);
        add_action('wp_delete_post', [$this, 'handleProductDelete'], 10, 1);
        add_action('wp_trash_post', [$this, 'handleProductDelete'], 10, 1);
        add_action('admin_init', [$this, 'checkImportCompletion']);
    }

    /**
     * Get all cached product mappings
     */
    public function all(): array
    {
        return Cache::remember($this->cacheKey, $this->ttl, function () {
            return $this->fetchFromDatabase();
        });
    }

    /**
     * Get product mapping by WooCommerce product ID
     */
    public function getByProductId(int $productId): ?array
    {
        $products = $this->all();

        return $products[$productId] ?? null;
    }

    /**
     * Get product mapping by Lightspeed ID
     */
    public function getByLightspeedId(int|string $lightspeedId): ?array
    {
        $products = $this->all();

        foreach ($products as $productId => $data) {
            if (isset($data['lightspeed_id']) && $data['lightspeed_id'] == $lightspeedId) {
                return array_merge(['product_id' => $productId], $data);
            }
        }

        return null;
    }

    /**
     * Get WooCommerce product ID by Lightspeed ID
     */
    public function getProductId(int|string $lightspeedId): ?int
    {
        $mapping = $this->getByLightspeedId($lightspeedId);

        return $mapping['product_id'] ?? null;
    }

    /**
     * Get Lightspeed ID by WooCommerce product ID
     */
    public function getLightspeedId(int $productId): ?string
    {
        $mapping = $this->getByProductId($productId);

        return $mapping['lightspeed_id'] ?? null;
    }

    /**
     * Get all Lightspeed IDs as a collection
     */
    public function getLightspeedIds(): Collection
    {
        return collect($this->all())->pluck('lightspeed_id')->filter();
    }

    /**
     * Get all WooCommerce product IDs as a collection
     */
    public function getProductIds(): Collection
    {
        return collect($this->all())->keys();
    }

    /**
     * Check if a product exists in the cache
     */
    public function hasProduct(int $productId): bool
    {
        return isset($this->all()[$productId]);
    }

    /**
     * Update a single product in the cache
     */
    public function updateProductData(int $productId, array $data): bool
    {
        $products = $this->all();

        if (! isset($products[$productId])) {
            return false;
        }

        $products[$productId] = array_merge($products[$productId], $data);

        return Cache::put($this->cacheKey, $products, $this->ttl);
    }

    /**
     * Refresh the entire cache from database
     */
    public function refresh(): array
    {
        $products = $this->fetchFromDatabase();
        Cache::put($this->cacheKey, $products, $this->ttl);

        return $products;
    }

    /**
     * Clear the cache
     */
    public function clear(): void
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Get cache metadata/statistics
     */
    public function getStats(): array
    {
        $products = $this->all();

        return [
            'total_products' => count($products),
            'cache_key' => $this->cacheKey,
            'ttl_seconds' => $this->ttl,
        ];
    }

    /**
     * Handle new product creation
     */
    public function handleNewProduct(int $productId): void
    {
        if ($this->shouldSkipCacheUpdate()) {
            return;
        }

        $lightspeedId = get_field('lightspeed_id', $productId);

        if (! $lightspeedId) {
            return;
        }

        $product = wc_get_product($productId);

        if (! $product) {
            return;
        }

        $products = $this->all();
        $products[$productId] = $this->buildProductData($product, $lightspeedId);

        Cache::put($this->cacheKey, $products, $this->ttl);
    }

    /**
     * Handle product update
     */
    public function handleProductUpdate(int $productId, WC_Product $product): void
    {
        if ($this->shouldSkipCacheUpdate()) {
            return;
        }

        $lightspeedId = get_field('lightspeed_id', $productId);

        if (! $lightspeedId) {
            $this->handleProductDelete($productId);

            return;
        }

        $products = $this->all();
        $products[$productId] = $this->buildProductData($product, $lightspeedId);

        Cache::put($this->cacheKey, $products, $this->ttl);
    }

    /**
     * Handle product deletion
     */
    public function handleProductDelete(int $productId): void
    {
        if (get_post_type($productId) !== 'product') {
            return;
        }

        $products = $this->all();

        if (isset($products[$productId])) {
            unset($products[$productId]);
            Cache::put($this->cacheKey, $products, $this->ttl);
        }
    }

    /**
     * Check if product import has completed and refresh cache
     */
    public function checkImportCompletion(): void
    {
        if ($this->shouldSkipCacheUpdate()) {
            return;
        }

        if (! current_user_can('administrator')) {
            return;
        }

        $isImportComplete = isset($_GET['products-imported'])
            || isset($_GET['products-updated'])
            || isset($_GET['products-failed'])
            || isset($_GET['products-skipped']);

        if ($isImportComplete) {
            $this->refresh();
        }
    }

    /**
     * Check if cache updates should be skipped
     */
    private function shouldSkipCacheUpdate(): bool
    {
        if (defined('LIGHTSPEED_SYNC_IN_PROGRESS')) {
            return true;
        }

        return $this->isImportRequest();
    }

    /**
     * Check if current request is part of an import
     */
    private function isImportRequest(): bool
    {
        if (! is_admin()) {
            return false;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'woocommerce_do_ajax_product_import') {
            return true;
        }

        if (isset($_GET['post_type']) && $_GET['post_type'] === 'product'
            && isset($_GET['page']) && $_GET['page'] === 'product_importer') {
            return true;
        }

        return false;
    }

    /**
     * Build product data array for caching
     */
    private function buildProductData(WC_Product $product, string $lightspeedId): array
    {
        return [
            'lightspeed_id' => $lightspeedId,
            'stock' => $product->get_stock_quantity() ?? 0,
            'price' => $product->get_price(),
            'last_synced_at' => null,
        ];
    }

    /**
     * Fetch all products with Lightspeed IDs from database
     */
    private function fetchFromDatabase(): array
    {
        $metaKey = config('lightspeed.woocommerce.lightspeed_id_meta_key', 'lightspeed_id');

        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => $metaKey,
                    'value' => '',
                    'compare' => '!=',
                ],
            ],
        ];

        $query = new WP_Query($args);
        $products = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $productId) {
                $product = wc_get_product($productId);

                if (! $product) {
                    continue;
                }

                $lightspeedId = get_field($metaKey, $productId);

                if (! $lightspeedId) {
                    continue;
                }

                $products[$productId] = $this->buildProductData($product, $lightspeedId);
            }
        }

        wp_reset_postdata();

        return $products;
    }
}
