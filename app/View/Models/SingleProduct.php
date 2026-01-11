<?php

namespace App\View\Models;

use App\Services\UspService;
use App\View\Factories\ProductAttributeFactory;
use Illuminate\Support\Collection;
use WC_Product;

class SingleProduct extends Product
{
    public readonly array $galleryIds;
    public readonly array $allImages;
    public readonly string $shortDescription;
    public readonly string $description;
    public readonly ?string $sku;
    public readonly array $variationIds;
    public readonly array $allReviews;
    public readonly array $displayReviews;

    public readonly array $attributes;
    public readonly array $usps;
    public readonly ?Collection $upsellProducts;
    public readonly array $sizeVariations;

    public function __construct(WC_Product $product)
    {
        parent::__construct($product);

        // Gallery
        $this->galleryIds = $product->get_gallery_image_ids();
        $this->allImages = $this->imageId
            ? array_merge([$this->imageId], $this->galleryIds)
            : $this->galleryIds;

        // Basic fields
        $this->shortDescription = $product->get_short_description();
        $this->description = $product->get_description();
        $this->sku = $product->get_sku();

        $this->attributes = collect(
            ProductAttributeFactory::build($product)
        )
            ->map(fn ($dto) => $dto->toArray())
            ->values()
            ->all();

        // Get variation IDs for size variations and reviews display
        $this->variationIds = self::getVariationIdsForReviews($product);

        // Size variations (other products with same name)
        $this->sizeVariations = self::getSizeVariations($this->variationIds, $product->get_id());

        // Get reviews for display (rating/count inherited from parent Product)
        $reviewData = self::getReviewsForDisplay($this->variationIds);
        $this->allReviews = $reviewData['all_reviews'];
        $this->displayReviews = $reviewData['display_reviews'];

        // USPs
        $this->usps = UspService::productUsps();

        // Upsells
        $this->upsellProducts = collect($product->get_upsell_ids())
            ->map(fn ($id) => Product::find($id))
            ->filter();
    }

    public static function find(int|WC_Product $product): ?self
    {
        if (is_int($product)) {
            $product = wc_get_product($product);
        }

        return $product instanceof WC_Product ? new self($product) : null;
    }

    public function imageCount(): int
    {
        return count($this->allImages);
    }

    public function hasMultipleImages(): bool
    {
        return $this->imageCount() > 1;
    }

    public function hasReviews(): bool
    {
        return $this->reviewCount > 0;
    }

    public function hasUpsells(): bool
    {
        return $this->upsellProducts?->isNotEmpty() ?? false;
    }

    /**
     * Find all product IDs with the same name for review aggregation.
     */
    protected static function getVariationIdsForReviews(WC_Product $product): array
    {
        global $wpdb;

        return $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts
             WHERE post_title = %s
             AND post_type = 'product'
             AND post_status = 'publish'",
            $product->get_name()
        ));
    }

    /**
     * Get reviews for display on single product page.
     * Rating and count are inherited from parent Product model.
     */
    protected static function getReviewsForDisplay(array $variationIds): array
    {
        $allReviews = get_comments([
            'post__in' => $variationIds,
            'status' => 'approve',
            'type' => 'review',
        ]);

        $displayReviews = get_comments([
            'post__in' => $variationIds,
            'status' => 'approve',
            'type' => 'review',
            'number' => 10,
            'orderby' => 'comment_date',
            'order' => 'DESC',
        ]);

        return [
            'all_reviews' => $allReviews,
            'display_reviews' => $displayReviews,
        ];
    }

    /**
     * Get size variations data for all products with the same name.
     */
    protected static function getSizeVariations(array $productIds, int $currentProductId): array
    {
        $variations = [];

        foreach ($productIds as $productId) {
            $wcProduct = wc_get_product($productId);
            if (! $wcProduct || $wcProduct->get_status() !== 'publish') {
                continue;
            }

            $contents = get_field('product_contents', $productId) ?: '';
            $regularPrice = $wcProduct->get_regular_price();
            $salePrice = $wcProduct->get_sale_price();
            $isOnSale = $wcProduct->is_on_sale();

            $variations[] = [
                'id' => (int) $productId,
                'contents' => $contents,
                'url' => $wcProduct->get_permalink(),
                'is_current' => (int) $productId === $currentProductId,
                'is_on_sale' => $isOnSale,
                'price' => $isOnSale ? $salePrice : $regularPrice,
                'price_formatted' => wc_price($isOnSale ? $salePrice : $regularPrice),
                'regular_price' => $regularPrice,
                'regular_price_formatted' => wc_price($regularPrice),
                'is_in_stock' => $wcProduct->is_in_stock(),
            ];
        }

        // Sort by price ascending
        usort($variations, fn ($a, $b) => $a['price'] <=> $b['price']);

        return $variations;
    }

    /**
     * Check if there are multiple size variations available.
     */
    public function hasSizeVariations(): bool
    {
        return count($this->sizeVariations) > 1;
    }
}
