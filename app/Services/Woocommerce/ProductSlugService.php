<?php

namespace App\Services\Woocommerce;

/**
 * Service to generate product slugs with contents field.
 *
 * Appends the product_contents ACF field to product slugs to create
 * unique URLs for products with the same name but different contents
 * (size variants). Works with both manual saves and CSV imports.
 */
class ProductSlugService
{
    /**
     * Flag to prevent recursive slug updates.
     */
    protected static bool $isUpdating = false;

    /**
     * Register hooks for slug generation.
     */
    public static function register(): void
    {
        // CSV Importer - fires after product + meta are imported
        add_action(
            'woocommerce_product_import_inserted_product_object',
            [self::class, 'onProductImported'],
            20,
            2
        );

        // New product created via WooCommerce
        add_action('woocommerce_new_product', [self::class, 'onProductSaved'], 20, 2);

        // Existing product updated via WooCommerce
        add_action('woocommerce_update_product', [self::class, 'onProductSaved'], 20, 2);

        // ACF fields saved (handles cases where only ACF is updated)
        add_action('acf/save_post', [self::class, 'onAcfSave'], 20, 1);
    }

    /**
     * Handle product import from CSV.
     */
    public static function onProductImported(\WC_Product $product, array $data): void
    {
        self::maybeUpdateSlug($product->get_id());
    }

    /**
     * Handle product save via WooCommerce.
     */
    public static function onProductSaved(int $productId, \WC_Product $product): void
    {
        self::maybeUpdateSlug($productId);
    }

    /**
     * Handle ACF field save.
     */
    public static function onAcfSave(int|string $postId): void
    {
        // ACF passes taxonomy terms as strings like "product_cat_123" or "term_123"
        if (!is_numeric($postId)) {
            return;
        }

        $postId = (int) $postId;

        if (get_post_type($postId) !== 'product') {
            return;
        }

        self::maybeUpdateSlug($postId);
    }

    /**
     * Update the product slug if conditions are met.
     */
    protected static function maybeUpdateSlug(int $productId): void
    {
        // Prevent recursive updates
        if (self::$isUpdating) {
            return;
        }

        $product = wc_get_product($productId);
        if (! $product) {
            return;
        }

        $contents = get_field('product_contents', $productId);

        // If no contents, leave slug as-is (WordPress default)
        if (empty($contents)) {
            return;
        }

        $newSlug = self::generateSlug($product->get_name(), $contents, $productId);
        $currentSlug = $product->get_slug();

        // Only update if slug would change
        if ($currentSlug === $newSlug) {
            return;
        }

        self::$isUpdating = true;

        try {
            wp_update_post([
                'ID' => $productId,
                'post_name' => $newSlug,
            ]);
        } finally {
            self::$isUpdating = false;
        }
    }

    /**
     * Generate a unique slug from name and contents.
     */
    protected static function generateSlug(string $name, string $contents, int $productId): string
    {
        // Sanitize both parts for URL
        $namePart = sanitize_title($name);
        $contentsPart = sanitize_title($contents);

        // Combine into desired format
        $desiredSlug = $namePart . '-' . $contentsPart;

        // Use WordPress function to ensure uniqueness
        return wp_unique_post_slug(
            $desiredSlug,
            $productId,
            'publish',
            'product',
            0
        );
    }
}
