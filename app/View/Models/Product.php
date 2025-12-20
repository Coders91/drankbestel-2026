<?php

namespace App\View\Models;

use Livewire\Wireable;
use App\Support\Money;
use App\View\Models\ProductPrice;

use WC_Product;

readonly class Product implements Wireable
{
    public function __construct(
        public int          $id,
        public string       $name,
        public string       $title,
        public string       $url,
        public bool         $is_on_sale,
        public bool         $boxed,
        public ?string      $contents,
        public ProductPrice $price,
        public int|string   $imageId,
    ) {}

    public static function find(int|WC_Product $product): ?self
    {
        // Already a typed product → return as-is
        if ($product instanceof self) {
            return $product;
        }

        // Convert ID to WC_Product
        if (is_int($product)) {
            $product = wc_get_product($product);
        }

        if (! $product instanceof WC_Product) {
            return null;
        }

        $product_id = $product->get_id();
        $product_contents = get_field('product_contents', $product_id);

        $is_on_sale = $product->is_on_sale();

        return new self(
            id: $product_id,
            name: $product->get_name(),
            title: $product->get_name() . ' ' . $product_contents,
            url: $product->get_permalink(),
            is_on_sale: $is_on_sale,
            boxed: $product->get_attribute('pa_doos') === 'Ja',
            contents: $product_contents,
            price: new ProductPrice(
                regular: Money::from($product->get_regular_price()),
                sale: $is_on_sale
                    ? Money::from($product->get_sale_price())
                    : null,
                is_on_sale: $is_on_sale,
            ),
            imageId: $product->get_image_id(),
        );
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
