<?php

namespace App\Services\StructuredData\Builders;

use App\Services\StructuredData\Concerns\HasSchemaIdentifiers;
use App\Services\Woocommerce\ShippingZoneService;
use App\View\Models\SingleProduct;
use Spatie\SchemaOrg\Offer;
use Spatie\SchemaOrg\Product;
use Spatie\SchemaOrg\ProductGroup;
use Spatie\SchemaOrg\Schema;

class ProductBuilder
{
    use HasSchemaIdentifiers;

    public function build(SingleProduct $product): Product|ProductGroup
    {
        // Use ProductGroup for products with size variations
        if ($product->hasSizeVariations()) {
            return $this->buildProductGroup($product);
        }

        return $this->buildSingleProduct($product);
    }

    protected function buildProductGroup(SingleProduct $product): ProductGroup
    {
        $schema = Schema::productGroup()
            ->setProperty('@id', $this->productGroupId($product->id))
            ->name($product->name)
            ->url($product->url)
            ->description(wp_strip_all_tags($product->shortDescription ?: $product->description))
            ->productGroupID(sanitize_title($product->name))
            ->variesBy(['https://schema.org/size']);

        // Category
        $category = $this->getProductCategory($product);
        if ($category) {
            $schema->category($category);
        }

        // Build variant products
        $variants = $this->buildVariantProducts($product);
        $schema->hasVariant($variants);

        // Aggregate Rating (combined from all size variants)
        if ($product->reviewCount > 0) {
            $schema->aggregateRating(
                Schema::aggregateRating()
                    ->ratingValue(round($product->rating, 1))
                    ->reviewCount($product->reviewCount)
                    ->bestRating(5)
                    ->worstRating(1)
            );
        }

        // Individual Reviews (up to 10)
        $reviews = $this->buildReviews($product);
        if (! empty($reviews)) {
            $schema->review($reviews);
        }

        return $schema;
    }

    protected function buildVariantProducts(SingleProduct $product): array
    {
        $variants = [];

        foreach ($product->sizeVariations as $variation) {
            $variantProduct = Schema::product()
                ->setProperty('@id', $this->productId($variation['id']))
                ->name($product->name . ' ' . $variation['contents'])
                ->description(wp_strip_all_tags($product->shortDescription ?: $product->description))
                ->url($variation['url'])
                ->size($variation['contents']);

            // Get variant-specific image
            $variantImageId = get_post_thumbnail_id($variation['id']);
            if ($variantImageId) {
                $imageUrl = wp_get_attachment_image_url($variantImageId, 'large');
                if ($imageUrl) {
                    $variantProduct->image($imageUrl);
                }
            }

            // SKU for variant
            $variantWcProduct = wc_get_product($variation['id']);
            if ($variantWcProduct && $variantWcProduct->get_sku()) {
                $variantProduct->sku($variantWcProduct->get_sku());
            }

            // GTIN/EAN on Product level for variant
            $ean = get_field('product_ean', $variation['id']);
            if ($ean) {
                $variantProduct->gtin13($ean);
            }

            // Single offer for this variant
            $offer = $this->buildSingleOffer(
                productId: $variation['id'],
                price: (float) $variation['price'],
                inStock: $variation['is_in_stock'],
                url: $variation['url'],
            );
            $variantProduct->offers($offer);

            $variants[] = $variantProduct;
        }

        return $variants;
    }

    protected function buildSingleProduct(SingleProduct $product): Product
    {
        $schema = Schema::product()
            ->setProperty('@id', $this->productId($product->id))
            ->name($product->title)
            ->url($product->url)
            ->description(wp_strip_all_tags($product->shortDescription ?: $product->description));

        // SKU
        if ($product->sku) {
            $schema->sku($product->sku);
        }

        // Images
        $images = $this->getProductImages($product);
        if (! empty($images)) {
            $schema->image($images);
        }

        // Brand
        $brand = $this->getProductBrand($product->id);
        if ($brand) {
            $schema->brand(Schema::brand()->name($brand));
        }

        // Category
        $category = $this->getProductCategory($product);
        if ($category) {
            $schema->category($category);
        }

        // GTIN/EAN on Product level
        $ean = get_field('product_ean', $product->id);
        if ($ean) {
            $schema->gtin13($ean);
        }

        // Single offer
        $offer = $this->buildSingleOffer(
            productId: $product->id,
            price: $product->price->sale?->decimal() ?? $product->price->regular->decimal(),
            inStock: $product->is_in_stock,
            url: $product->url,
        );
        $schema->offers($offer);

        // Aggregate Rating
        if ($product->reviewCount > 0) {
            $schema->aggregateRating(
                Schema::aggregateRating()
                    ->ratingValue(round($product->rating, 1))
                    ->reviewCount($product->reviewCount)
                    ->bestRating(5)
                    ->worstRating(1)
            );
        }

        // Individual Reviews (up to 10)
        $reviews = $this->buildReviews($product);
        if (! empty($reviews)) {
            $schema->review($reviews);
        }

        return $schema;
    }

    protected function getProductImages(SingleProduct $product): array
    {
        $images = [];

        foreach ($product->allImages as $imageId) {
            $url = wp_get_attachment_image_url($imageId, 'large');
            if ($url) {
                $images[] = $url;
            }
        }

        return $images;
    }

    protected function getProductBrand(int $productId): ?string
    {
        // Try product_brand taxonomy first
        $brands = wp_get_post_terms($productId, 'product_brand', ['fields' => 'names']);
        if (! empty($brands) && ! is_wp_error($brands)) {
            return $brands[0];
        }

        // Fallback to pa_merk attribute
        $wcProduct = wc_get_product($productId);
        if ($wcProduct) {
            $merk = $wcProduct->get_attribute('pa_merk');
            if ($merk) {
                return $merk;
            }
        }

        return null;
    }

    protected function getProductCategory(SingleProduct $product): ?string
    {
        if (empty($product->categories)) {
            return null;
        }

        // Get the deepest category
        $primary = $product->categories[count($product->categories) - 1];

        return $primary->name ?? null;
    }

    protected function buildSingleOffer(
        int $productId,
        float $price,
        bool $inStock,
        string $url,
    ): Offer {
        $offer = Schema::offer()
            ->setProperty('@id', $this->offerId($productId))
            ->url($url)
            ->priceCurrency('EUR')
            ->price(number_format($price, 2, '.', ''))
            ->availability($inStock
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock'
            )
            ->seller(['@id' => $this->organizationId()])
            ->itemCondition('https://schema.org/NewCondition')
            ->priceValidUntil(date('Y-m-d', strtotime('+30 days')));

        // Shipping Details
        $offer->setProperty('shippingDetails', $this->buildShippingDetails());

        return $offer;
    }

    protected function buildShippingDetails(): array
    {
        return [
            '@type' => 'OfferShippingDetails',
            'shippingRate' => [
                '@type' => 'MonetaryAmount',
                'value' => ShippingZoneService::flatRateCost(),
                'currency' => 'EUR',
            ],
            'shippingDestination' => [
                '@type' => 'DefinedRegion',
                'addressCountry' => 'NL',
            ],
            'deliveryTime' => [
                '@type' => 'ShippingDeliveryTime',
                'handlingTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => '0',
                    'maxValue' => '1',
                    'unitCode' => 'DAY',
                ],
                'transitTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => '1',
                    'maxValue' => '3',
                    'unitCode' => 'DAY',
                ],
            ],
        ];
    }

    protected function buildReviews(SingleProduct $product): array
    {
        $reviews = [];

        foreach (array_slice($product->displayReviews, 0, 10) as $comment) {
            $rating = get_comment_meta($comment->comment_ID, 'rating', true);

            if (! $rating) {
                continue;
            }

            $reviews[] = Schema::review()
                ->author(
                    Schema::person()->name($comment->comment_author)
                )
                ->datePublished($comment->comment_date)
                ->reviewBody(wp_strip_all_tags($comment->comment_content))
                ->reviewRating(
                    Schema::rating()
                        ->ratingValue((int) $rating)
                        ->bestRating(5)
                        ->worstRating(1)
                );
        }

        return $reviews;
    }
}
