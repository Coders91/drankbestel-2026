<?php

namespace App\View\Models;

use Livewire\Wireable;
use App\Support\Money;
use App\View\Models\ProductPrice;

use WC_Product;

class Product implements Wireable
{
    /**
     * Cache for aggregated ratings by product name to avoid repeated queries.
     */
    protected static array $ratingCache = [];

    public readonly int $id;
    public readonly string $name;
    public readonly string $title;
    public readonly string $url;
    public readonly array $categories;
    public readonly bool $is_on_sale;
    public readonly ?float $discountPercentage;
    public readonly bool $boxed;
    public readonly ?string $contents;
    public readonly ProductPrice $price;
    public readonly int|string $imageId;
    public readonly ?int $stockQuantity;
    public readonly bool $is_in_stock;
    public readonly float $rating;
    public readonly int $reviewCount;
    public readonly bool $isNew;
    public readonly bool $soldAsPack;
    public readonly int $packSize;

    public function __construct(WC_Product $product)
    {
        $this->id = $product->get_id();
        $this->name = $product->get_name();
        $this->url = $product->get_permalink();
        $this->contents = get_field('product_contents', $this->id);
        $this->title = $this->name . ' ' . trim($this->contents);
        $this->categories = wc_get_product_terms($this->id, 'product_cat', ['orderby' => 'parent', 'order' => 'ASC']);
        $this->boxed = $product->get_attribute('pa_doos') === 'Ja';
        $this->is_on_sale = $product->is_on_sale();

        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();

        $this->discountPercentage = $this->is_on_sale
            ? round((($regular_price - $sale_price) / $regular_price) * 100)
            : null;

        $this->price = new ProductPrice(
            regular: Money::from($regular_price),
            sale: $this->is_on_sale ? Money::from($sale_price) : null,
        );

        $this->imageId = $product->get_image_id();
        $this->stockQuantity = $product->get_stock_quantity();
        $this->is_in_stock = $product->is_in_stock();

        // Use aggregated rating from all size variants (products with same name)
        $aggregatedRating = self::getAggregatedRating($this->name);
        $this->rating = $aggregatedRating['average_rating'];
        $this->reviewCount = $aggregatedRating['review_count'];

        $dateCreated = $product->get_date_created();
        $this->isNew = $dateCreated && $dateCreated->getTimestamp() >= strtotime('-30 days');

        $this->soldAsPack = (bool) get_field('product_sold_as_pack', $this->id);
        $this->packSize = (int) (get_field('product_pack_size', $this->id) ?: 1);
    }

    /**
     * Get aggregated rating data for all products with the same name.
     * Results are cached to avoid repeated queries for products in grids.
     */
    protected static function getAggregatedRating(string $productName): array
    {
        // Return cached result if available
        if (isset(self::$ratingCache[$productName])) {
            return self::$ratingCache[$productName];
        }

        global $wpdb;

        // Find all product IDs with the same name
        $productIds = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts
             WHERE post_title = %s
             AND post_type = 'product'
             AND post_status = 'publish'",
            $productName
        ));

        if (empty($productIds)) {
            self::$ratingCache[$productName] = [
                'average_rating' => 0.0,
                'review_count' => 0,
            ];

            return self::$ratingCache[$productName];
        }

        // Get all approved reviews for these products
        $reviews = get_comments([
            'post__in' => $productIds,
            'status' => 'approve',
            'type' => 'review',
        ]);

        $reviewCount = count($reviews);
        $averageRating = 0.0;

        if ($reviewCount > 0) {
            $totalRating = 0;
            foreach ($reviews as $review) {
                $totalRating += (int) get_comment_meta($review->comment_ID, 'rating', true);
            }
            $averageRating = $totalRating / $reviewCount;
        }

        self::$ratingCache[$productName] = [
            'average_rating' => $averageRating,
            'review_count' => $reviewCount,
        ];

        return self::$ratingCache[$productName];
    }

    /**
     * Prime WordPress object caches for a batch of product IDs.
     * Call this before looping Product::find() to avoid N+1 queries.
     */
    public static function primeCache(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        // Prime post and post meta caches in bulk
        _prime_post_caches($ids, true, true);

        // Prime term caches for all products at once
        wp_cache_get_multiple(array_map(fn ($id) => "product_cat_relationships_{$id}", $ids), 'terms');
        update_object_term_cache($ids, 'product');

        // Prime aggregated ratings in batch
        self::primeRatingCache($ids);
    }

    /**
     * Batch-load aggregated ratings for multiple products.
     */
    protected static function primeRatingCache(array $ids): void
    {
        global $wpdb;

        if (empty($ids)) {
            return;
        }

        // Get names for the given IDs that aren't already cached
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $names = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT post_title FROM $wpdb->posts WHERE ID IN ($placeholders) AND post_type = 'product' AND post_status = 'publish'",
            ...$ids
        ));

        $uncachedNames = array_filter($names, fn ($name) => !isset(self::$ratingCache[$name]));

        if (empty($uncachedNames)) {
            return;
        }

        // Find all product IDs matching these names
        $namePlaceholders = implode(',', array_fill(0, count($uncachedNames), '%s'));
        $allProductIds = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_title FROM $wpdb->posts WHERE post_title IN ($namePlaceholders) AND post_type = 'product' AND post_status = 'publish'",
            ...$uncachedNames
        ));

        $idsByName = [];
        foreach ($allProductIds as $row) {
            $idsByName[$row->post_title][] = (int) $row->ID;
        }

        // Batch-fetch all reviews for these product IDs
        $allIds = array_merge(...array_values($idsByName));

        if (empty($allIds)) {
            foreach ($uncachedNames as $name) {
                self::$ratingCache[$name] = ['average_rating' => 0.0, 'review_count' => 0];
            }
            return;
        }

        $reviews = get_comments([
            'post__in' => $allIds,
            'status' => 'approve',
            'type' => 'review',
        ]);

        // Group reviews by product name
        $reviewsByName = [];
        $postIdToName = [];
        foreach ($allProductIds as $row) {
            $postIdToName[(int) $row->ID] = $row->post_title;
        }

        foreach ($reviews as $review) {
            $name = $postIdToName[(int) $review->comment_post_ID] ?? null;
            if ($name) {
                $reviewsByName[$name][] = $review;
            }
        }

        // Calculate ratings per name
        foreach ($uncachedNames as $name) {
            $nameReviews = $reviewsByName[$name] ?? [];
            $count = count($nameReviews);
            $avg = 0.0;

            if ($count > 0) {
                $total = 0;
                foreach ($nameReviews as $review) {
                    $total += (int) get_comment_meta($review->comment_ID, 'rating', true);
                }
                $avg = $total / $count;
            }

            self::$ratingCache[$name] = ['average_rating' => $avg, 'review_count' => $count];
        }
    }

    public static function find(int|WC_Product $product): ?self
    {
        if ($product instanceof self) {
            return $product;
        }

        if (is_int($product)) {
            $product = wc_get_product($product);
        }

        return $product instanceof WC_Product ? new self($product) : null;
    }

    public function toLivewire(): array
    {
        return ['id' => $this->id];
    }

    public static function fromLivewire($value): ?Product
    {
        $product = wc_get_product($value);
        return $product ? self::find($product) : null;
    }
}
